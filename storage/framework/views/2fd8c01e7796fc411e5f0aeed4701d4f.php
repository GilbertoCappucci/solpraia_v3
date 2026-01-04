<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sol & Praia - Gestão para Vendas na Praia</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #FF9F1C;
            /* Vibrant Orange */
            --secondary: #2EC4B6;
            /* Tiffany Blue/Teal */
            --accent: #FFBF69;
            /* Mellow Yellow */
            --dark: #1F2421;
            --light: #FFFFFF;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--dark);
            color: var(--light);
            overflow-x: hidden;
        }

        /* Utilities */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 32px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 159, 28, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 159, 28, 0.6);
        }

        .btn-glass {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            color: white;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Header */
        header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
            padding: 20px 0;
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .logo span {
            color: var(--primary);
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.6)), url('/images/hero-bg.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .hero-content {
            max-width: 600px;
            animation: fadeUp 1s ease-out;
        }

        h1 {
            font-size: 4rem;
            line-height: 1.1;
            margin-bottom: 20px;
            font-weight: 800;
        }

        h1 span {
            color: var(--primary);
            display: block;
        }

        p.subtitle {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
            line-height: 1.6;
        }

        /* Features Grid */
        .features {
            padding: 100px 0;
            background: linear-gradient(to bottom, #1a1a1a, #2a2a2a);
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .section-title p {
            color: #ccc;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.08);
        }

        .icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--secondary);
        }

        .card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .card p {
            color: #aaa;
            line-height: 1.6;
        }

        /* Footer */
        footer {
            padding: 40px 0;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            color: #888;
        }

        /* Animations */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            h1 {
                font-size: 2.8rem;
            }

            .hero {
                background-attachment: scroll;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <div class="logo">Sol<span>&</span>Praia</div>
            <nav>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('login')): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(url('/home')); ?>" class="btn btn-glass">Dashboard</a>
                <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="btn btn-glass">Entrar</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Venda mais <br><span>com os pés na areia.</span></h1>
                <p class="subtitle">A ferramenta definitiva para quiosques, barracas e vendedores ambulantes. Gerencie pedidos, estoque e aumente seu faturamento enquanto aproveita o sol.</p>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(false): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('register')): ?>
                    <a href="<?php echo e(route('register')); ?>" class="btn btn-primary">Começar Agora</a>
                    <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-primary">Acessar Sistema</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Tudo o que você precisa</h2>
                <p>Feito pensar na agilidade do seu negócio.</p>
            </div>

            <div class="grid">
                <div class="card">
                    <div class="icon">📦</div>
                    <h3>Controle de Estoque</h3>
                    <p>Evite prejuízos e saiba exatamente o que tem no cooler ou na geladeira em tempo real.</p>
                </div>
                <div class="card">
                    <div class="icon">⚡</div>
                    <h3>Pedidos Ágeis</h3>
                    <p>Lance vendas em segundos. Menos tempo no celular, mais tempo atendendo o cliente.</p>
                </div>
                <div class="card">
                    <div class="icon">📊</div>
                    <h3>Relatórios Diários</h3>
                    <p>Acompanhe seu desempenho de vendas diário e mensal com gráficos simples.</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; <?php echo e(date('Y')); ?> Sol & Praia - App para Vendedores de Praia.</p>
        </div>
    </footer>
</body>

</html><?php /**PATH E:\Projects\solpraia\resources\views/welcome.blade.php ENDPATH**/ ?>