# Orientações para GitHub Copilot - Projeto Sol

## Contexto do Projeto

O **Sol** é um sistema de gestão digital mobile desenvolvido especificamente para barracas e quiosques de praia. O objetivo principal é facilitar a administração durante os períodos de alta temporada, oferecendo uma solução completa e integrada.

### Domínio de Negócio

O sistema atende às necessidades específicas do ambiente de praia, onde:

- **Mobilidade** é essencial - garçons circulam pela areia atendendo clientes em mesas e guarda-sóis
- **Velocidade** no atendimento é crucial durante picos de movimento
- **Integração** entre cozinha, bar, atendimento e caixa deve ser seamless
- **Controle financeiro** precisa ser rigoroso para múltiplos pontos de venda
- **Gestão de clientes** pode incluir sistemas de "pendura" (fiado) comuns em praias

### Fluxo Operacional Típico

1. **Cliente chega** → Abre um guarda sol / mes e atendente registra no dispositivo móvel
2. **Pedido feito** → Enviado automaticamente para cozinha/bar/caixa/adm
3. **Acompanhamento do Pedido** → Acompanhamento em tempo real do status dos pedidos
4. **Entrega** → Confirmação no sistema móvel
5. **Fechamento** → Integração com caixa e controle financeiro

## Estrutura do Sistema

### 1. Área Administrativa

#### 1.1 Cadastros
- **Usuário**
  -Root
  -Admin
  -Employee
  -Customer
  -Device
- **Pontos de vendas**
  - Mesa
  - Guarda-sol
- **Área de produção**
  - Bar
  - Cozinha
- **Categoria de produto**
- **Produto**
- **Menu de produto**
- **Financeiro**
  - Caixa
- **Cliente**

#### 1.2 Configuração
#### 1.3 Manutenção da assinatura
#### 1.4 Relatórios

### 2. Produção
- **Cozinha**
- **Bar**

### 3. Atendimento
- **Dispositivo**
- **Atendente**

### 4. Caixa
- **Ponto de venda**
  - Fechamento do ponto de venda
  - União de ponto de venda
  - Pendura

### 5. Gerenciamento

#### 5.1 Expeditor
- **Pedido**
- **Produção**
- **Caixa**

#### 5.2 Configuração
- **Ponto de venda**
  - Abertura
  - Fechamento

## Stack Tecnológica

| Componente | Tecnologia |
|------------|------------|
| **Backend** | Laravel 12|
| **Frontend** | Livewire + Flux UI |
| **UI Framework** | Filament |
| **Estilização** | Tailwind CSS |
| **Banco de dados** | MariaDB |

## Funcionalidades Específicas

### Características do Ambiente de Praia
- **Conectividade intermitente** - Sistema deve funcionar offline quando necessário
- **Dispositivos resistentes** - Interface otimizada para tablets/smartphones em ambiente externo
- **Usuários diversos** - Interface simples para funcionários com diferentes níveis técnicos
- **Horário de pico** - Sistema deve suportar alta concorrência durante horários movimentados

### Peculiaridades do Negócio
- **Pendura (Fiado)** - Sistema de crédito informal comum em praias
- **União de comandas** - Juntar múltiplos pontos de venda (mesa + guarda-sol)
- **Produtos sazonais** - Cardápio pode variar conforme época do ano
- **Controle por área** - Diferentes responsáveis para cozinha, bar, atendimento

## Diretrizes para Desenvolvimento

### Técnicas
- **Sempre utilizar o mcp Context7** para busca de documentações
- **Seguir os padrões do Laravel** para estrutura de código
- **Utilizar Livewire** para componentes interativos e atualizações em tempo real
- **Implementar design responsivo** com Tailwind CSS
- **Design mobile first** - priorizar experiência em dispositivos móveis
- **Manter consistência** com os componentes do Filament
- **Documentar adequadamente** todas as funcionalidades

### UX/UI Específicas
- **Botões grandes** para facilitar uso com dedos em telas touch
- **Cores contrastantes** para visibilidade sob sol forte
- **Navegação simples** com poucos cliques para ações frequentes
- **Feedback visual claro** para confirmação de ações
- **Modo offline** para funcionalidades críticas