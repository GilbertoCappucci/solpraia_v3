/**
 * Device Authorization Manager
 * Gerencia tokens de dispositivo no localStorage e comunicação com API
 */

class DeviceManager {
    constructor() {
        this.storageKey = 'restaurant_device_token';
        this.fingerprintKey = 'restaurant_device_fingerprint';
        this.apiBaseUrl = '/api/device';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        this.init();
    }

    /**
     * Inicializar o gerenciador
     */
    async init() {
        // Apenas mostrar modal se estamos na página de autorização
        if (window.location.pathname === '/device/authorization') {
            this.showAuthorizationForm();
            this.setupEventListeners();
            return;
        }

        // Verificar se já tem token válido (apenas validação, não mostra modal)
        const token = this.getStoredToken();
        if (token) {
            // Validar token silenciosamente
            await this.validateToken(token);
        }

        // Setup dos event listeners
        this.setupEventListeners();
    }

    /**
     * Verificar se a página atual requer autorização
     */
    requiresAuthorization() {
        // Esta função agora é apenas para referência
        // O controle real é feito pelo backend via middleware
        return window.location.pathname === '/device/authorization';
    }

    /**
     * Obter token armazenado
     */
    getStoredToken() {
        try {
            const data = localStorage.getItem(this.storageKey);
            if (!data) return null;

            const parsed = JSON.parse(data);
            
            // Verificar se não expirou
            if (parsed.expires_at && new Date(parsed.expires_at) < new Date()) {
                this.clearStoredData();
                return null;
            }

            return parsed.token;
        } catch (error) {
            console.error('Erro ao obter token:', error);
            this.clearStoredData();
            return null;
        }
    }

    /**
     * Armazenar token
     */
    storeToken(tokenData) {
        try {
            const data = {
                token: tokenData.token || tokenData.device_token,
                device_name: tokenData.name,
                expires_at: tokenData.expires_at,
                stored_at: new Date().toISOString(),
                device_id: tokenData.id
            };

            localStorage.setItem(this.storageKey, JSON.stringify(data));
            
            // Armazenar fingerprint se disponível
            if (tokenData.fingerprint) {
                localStorage.setItem(this.fingerprintKey, tokenData.fingerprint);
            }

            return true;
        } catch (error) {
            console.error('Erro ao armazenar token:', error);
            return false;
        }
    }

    /**
     * Limpar dados armazenados
     */
    clearStoredData() {
        localStorage.removeItem(this.storageKey);
        localStorage.removeItem(this.fingerprintKey);
    }

    /**
     * Gerar fingerprint do dispositivo
     */
    async generateFingerprint() {
        try {
            const response = await this.apiCall('/generate-fingerprint', 'POST');
            if (response.success) {
                localStorage.setItem(this.fingerprintKey, response.fingerprint);
                return response.fingerprint;
            }
        } catch (error) {
            console.error('Erro ao gerar fingerprint:', error);
        }
        
        // Fallback: gerar fingerprint simples no cliente
        return this.generateClientFingerprint();
    }

    /**
     * Gerar fingerprint simples no cliente
     */
    generateClientFingerprint() {
        const components = [
            navigator.userAgent,
            navigator.language,
            screen.width + 'x' + screen.height,
            new Date().getTimezoneOffset(),
            new Date().toDateString() // Muda diariamente
        ];
        
        const fingerprint = 'fp_' + this.simpleHash(components.join('|'));
        localStorage.setItem(this.fingerprintKey, fingerprint);
        return fingerprint;
    }

    /**
     * Hash simples
     */
    simpleHash(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32bit integer
        }
        return Math.abs(hash).toString(16);
    }

    /**
     * Validar token com a API
     */
    async validateToken(token) {
        try {
            const fingerprint = localStorage.getItem(this.fingerprintKey) || await this.generateFingerprint();
            
            const response = await this.apiCall('/validate', 'POST', {
                device_token: token,
                device_fingerprint: fingerprint
            });

            if (response.success) {
                // Atualizar dados armazenados
                this.storeToken({
                    token: token,
                    ...response.device
                });
                return true;
            }
            
            return false;
        } catch (error) {
            console.error('Erro na validação do token:', error);
            return false;
        }
    }

    /**
     * Registrar novo token
     */
    async registerToken(token) {
        try {
            const fingerprint = localStorage.getItem(this.fingerprintKey) || await this.generateFingerprint();
            
            console.log('Registrando token:', token);
            
            const response = await this.apiCall('/register', 'POST', {
                device_token: token,
                device_fingerprint: fingerprint
            });

            console.log('Resposta da API:', response);

            if (response.success) {
                console.log('Token registrado com sucesso!');
                
                // Armazenar token
                this.storeToken(response.device);
                
                // Recarregar página ou redirecionar
                this.onAuthorizationSuccess(response);
                return true;
            } else {
                console.error('Falha no registro:', response.message);
                this.showError(response.message);
                return false;
            }
        } catch (error) {
            console.error('Erro no registro do token:', error);
            this.showError('Erro na comunicação com o servidor');
            return false;
        }
    }

    /**
     * Fazer chamada para API
     */
    async apiCall(endpoint, method = 'GET', data = null) {
        const url = this.apiBaseUrl + endpoint;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        };

        // Adicionar CSRF token se disponível
        if (this.csrfToken) {
            options.headers['X-CSRF-TOKEN'] = this.csrfToken;
        }

        // Adicionar token de dispositivo se disponível
        const token = this.getStoredToken();
        if (token) {
            options.headers['X-Device-Token'] = token;
        }

        if (data) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);
        return await response.json();
    }

    /**
     * Mostrar formulário de autorização
     */
    showAuthorizationForm() {
        // Verificar se já existe o modal
        if (document.getElementById('device-authorization-modal')) {
            return;
        }

        const modal = this.createAuthorizationModal();
        document.body.appendChild(modal);
        
        // Mostrar modal
        setTimeout(() => {
            modal.classList.add('show');
        }, 100);
    }

    /**
     * Criar modal de autorização
     */
    createAuthorizationModal() {
        const modal = document.createElement('div');
        modal.id = 'device-authorization-modal';
        modal.className = 'device-modal';
        
        modal.innerHTML = `
            <div class="device-modal-backdrop">
                <div class="device-modal-content">
                    <div class="device-modal-header">
                        <h2>🔐 Autorização de Dispositivo</h2>
                        <p>Este dispositivo precisa ser autorizado para acessar o sistema</p>
                    </div>
                    
                    <div class="device-modal-body">
                        <div class="device-form-group">
                            <label for="device-token-input">Token de Autorização:</label>
                            <input 
                                type="text" 
                                id="device-token-input" 
                                placeholder="Digite o token fornecido pelo administrador"
                                class="device-input"
                            >
                            <small class="device-help">
                                Solicite um token de acesso ao administrador do sistema
                            </small>
                        </div>
                        
                        <div class="device-error-message" id="device-error" style="display: none;"></div>
                        
                        <div class="device-form-actions">
                            <button 
                                type="button" 
                                id="device-authorize-btn" 
                                class="device-btn device-btn-primary"
                            >
                                Autorizar Dispositivo
                            </button>
                        </div>
                    </div>
                    
                    <div class="device-modal-footer">
                        <small>
                            💡 <strong>Dica:</strong> O token será salvo neste navegador. 
                            Se limpar os dados do navegador, será necessário um novo token.
                        </small>
                    </div>
                </div>
            </div>
        `;

        // Adicionar estilos
        this.injectStyles();
        
        return modal;
    }

    /**
     * Setup dos event listeners
     */
    setupEventListeners() {
        // Event listener para o botão de autorizar (delegated event)
        document.addEventListener('click', (e) => {
            if (e.target.id === 'device-authorize-btn') {
                this.handleAuthorization();
            }
        });

        // Event listener para Enter no input
        document.addEventListener('keypress', (e) => {
            if (e.target.id === 'device-token-input' && e.key === 'Enter') {
                this.handleAuthorization();
            }
        });
    }

    /**
     * Lidar com autorização
     */
    async handleAuthorization() {
        const input = document.getElementById('device-token-input');
        const button = document.getElementById('device-authorize-btn');
        const token = input.value.trim();

        if (!token) {
            this.showError('Por favor, digite o token de autorização');
            return;
        }

        // Desabilitar botão
        button.disabled = true;
        button.textContent = 'Autorizando...';

        const success = await this.registerToken(token);

        if (!success) {
            // Reabilitar botão
            button.disabled = false;
            button.textContent = 'Autorizar Dispositivo';
        }
    }

    /**
     * Mostrar erro
     */
    showError(message) {
        const errorDiv = document.getElementById('device-error');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        } else {
            alert(message);
        }
    }

    /**
     * Sucesso na autorização
     */
    onAuthorizationSuccess(response) {
        console.log('Sucesso na autorização! Preparando redirect...');
        
        // Determinar URL de destino
        const urlParams = new URLSearchParams(window.location.search);
        const returnUrl = urlParams.get('return_url') || urlParams.get('redirect');
        const targetUrl = returnUrl || '/dashboard';
        
        console.log('URL de destino:', targetUrl);
        
        // Mostrar mensagem de sucesso
        const modal = document.getElementById('device-authorization-modal');
        const pageContent = document.querySelector('.container');
        
        // Atualizar o conteúdo da página
        if (pageContent) {
            pageContent.innerHTML = `
                <div class="header" style="background: linear-gradient(135deg, #4CAF50, #45a049);">
                    <h1>✅ Dispositivo Autorizado!</h1>
                    <p>Seu dispositivo foi autorizado com sucesso</p>
                </div>
                <div class="content" style="text-align: center;">
                    <p style="font-size: 18px; margin-bottom: 10px;"><strong>Dispositivo:</strong> ${response.device.name}</p>
                    ${response.device.expires_at ? `<p style="font-size: 16px;"><strong>Válido até:</strong> ${new Date(response.device.expires_at).toLocaleDateString('pt-BR')}</p>` : '<p style="font-size: 16px;"><strong>Validade:</strong> Permanente</p>'}
                    <p style="color: #4CAF50; font-weight: 600; margin-top: 20px; font-size: 18px;">Redirecionando...</p>
                </div>
            `;
        }
        
        if (modal) {
            modal.innerHTML = `
                <div class="device-modal-backdrop">
                    <div class="device-modal-content">
                        <div class="device-modal-header success">
                            <h2>✅ Dispositivo Autorizado!</h2>
                            <p>Seu dispositivo foi autorizado com sucesso</p>
                        </div>
                        <div class="device-modal-body">
                            <p><strong>Dispositivo:</strong> ${response.device.name}</p>
                            ${response.device.expires_at ? `<p><strong>Válido até:</strong> ${new Date(response.device.expires_at).toLocaleDateString('pt-BR')}</p>` : '<p><strong>Validade:</strong> Permanente</p>'}
                            <p class="device-success">Redirecionando...</p>
                        </div>
                    </div>
                </div>
            `;
        }

        // Redirecionar após 1.5 segundos
        setTimeout(() => {
            console.log('Redirecionando agora para:', targetUrl);
            window.location.href = targetUrl;
        }, 1500);
    }

    /**
     * Injetar estilos CSS
     */
    injectStyles() {
        if (document.getElementById('device-authorization-styles')) {
            return;
        }

        const styles = document.createElement('style');
        styles.id = 'device-authorization-styles';
        styles.textContent = `
            .device-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 10000;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            
            .device-modal.show {
                opacity: 1;
                visibility: visible;
            }
            
            .device-modal-backdrop {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .device-modal-content {
                background: white;
                border-radius: 12px;
                max-width: 500px;
                width: 100%;
                max-height: 90vh;
                overflow-y: auto;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                transform: scale(0.9);
                transition: transform 0.3s ease;
            }
            
            .device-modal.show .device-modal-content {
                transform: scale(1);
            }
            
            .device-modal-header {
                padding: 30px 30px 20px;
                text-align: center;
                border-bottom: 1px solid #eee;
            }
            
            .device-modal-header.success {
                background: linear-gradient(135deg, #4CAF50, #45a049);
                color: white;
                border-bottom: none;
            }
            
            .device-modal-header h2 {
                margin: 0 0 10px;
                font-size: 24px;
                font-weight: 600;
            }
            
            .device-modal-header p {
                margin: 0;
                opacity: 0.8;
            }
            
            .device-modal-body {
                padding: 30px;
            }
            
            .device-form-group {
                margin-bottom: 20px;
            }
            
            .device-form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
                color: #333;
            }
            
            .device-input {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #ddd;
                border-radius: 8px;
                font-size: 16px;
                transition: border-color 0.3s ease;
                box-sizing: border-box;
            }
            
            .device-input:focus {
                outline: none;
                border-color: #007cba;
            }
            
            .device-help {
                display: block;
                margin-top: 8px;
                color: #666;
                font-size: 14px;
            }
            
            .device-error-message {
                background: #ffebee;
                color: #c62828;
                padding: 12px 16px;
                border-radius: 6px;
                margin-bottom: 20px;
                border-left: 4px solid #c62828;
            }
            
            .device-form-actions {
                text-align: center;
            }
            
            .device-btn {
                padding: 12px 32px;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.3s ease;
                min-width: 150px;
            }
            
            .device-btn-primary {
                background: linear-gradient(135deg, #007cba, #006ba0);
                color: white;
            }
            
            .device-btn-primary:hover:not(:disabled) {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(0, 124, 186, 0.3);
            }
            
            .device-btn:disabled {
                opacity: 0.6;
                cursor: not-allowed;
                transform: none;
            }
            
            .device-modal-footer {
                padding: 20px 30px 30px;
                text-align: center;
                background: #f8f9fa;
                border-radius: 0 0 12px 12px;
            }
            
            .device-success {
                color: #4CAF50;
                font-weight: 500;
                margin-top: 15px;
            }
        `;
        
        document.head.appendChild(styles);
    }

    /**
     * Obter informações do dispositivo atual
     */
    async getDeviceInfo() {
        try {
            const response = await this.apiCall('/info');
            return response.success ? response.device : null;
        } catch (error) {
            console.error('Erro ao obter informações do dispositivo:', error);
            return null;
        }
    }

    /**
     * Verificar status do dispositivo periodicamente
     */
    startPeriodicCheck(intervalMinutes = 30) {
        setInterval(async () => {
            const token = this.getStoredToken();
            if (token) {
                const isValid = await this.validateToken(token);
                if (!isValid) {
                    this.clearStoredData();
                    this.showAuthorizationForm();
                }
            }
        }, intervalMinutes * 60 * 1000);
    }
}

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', () => {
    window.deviceManager = new DeviceManager();
    
    // Iniciar verificação periódica (a cada 30 minutos)
    window.deviceManager.startPeriodicCheck(30);
});

// Expor globalmente para uso em outros scripts
window.DeviceManager = DeviceManager;