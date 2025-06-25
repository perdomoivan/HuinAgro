<?php
define('HUINAGRO_SYSTEM', true);
require 'database.php';
require 'functions.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HuinAgro - Sistema de Gestión</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --huinagro-primary: #2a9df4; 
            --huinagro-accent: #2ecc71;  
            --huinagro-dark: #1b4f72;    
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
        }

        .huinagro-bg-primary {
            background-color: var(--huinagro-primary);
        }

        .huinagro-text-primary {
            color: var(--huinagro-primary);
        }

        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://via.placeholder.com/1920x600') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 8rem 0;
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--huinagro-accent);
            margin-bottom: 1rem;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        footer a:hover {
            color: var(--huinagro-accent);
        }
    </style>
</head>
<body>
  
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--huinagro-primary);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-fish me-2"></i>HuinAgro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Inicio</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Ingresar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

   
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Sistema de Gestión Acuícola</h1>
            <p class="lead mb-5">Control y seguimiento de producción pesquera</p>
            <a href="login.php" class="btn btn-success btn-lg px-4 me-2">Iniciar Sesión</a>
            
        </div>
    </section>


    <section class="py-5 bg-white">
        <div class="container text-center">
            <h2 class="fw-bold text-success mb-4">¿Por qué es clave la Acuicultura?</h2>
            <p class="lead text-muted mb-5">Un pilar de sostenibilidad, innovación y desarrollo para comunidades costeras e interiores.</p>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="p-4 border rounded shadow-sm h-100 card-hover">
                        <div class="mb-3">
                            <i class="fas fa-leaf feature-icon"></i>
                        </div>
                        <h4 class="fw-bold">Sostenibilidad Ambiental</h4>
                        <p class="text-muted">Reduce la presión sobre los océanos y promueve prácticas responsables para conservar los ecosistemas acuáticos.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 border rounded shadow-sm h-100 card-hover">
                        <div class="mb-3">
                            <i class="fas fa-fish feature-icon"></i>
                        </div>
                        <h4 class="fw-bold">Producción de Alimentos</h4>
                        <p class="text-muted">Proporciona proteína de alta calidad para millones de personas, con una huella ecológica menor que otras industrias.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 border rounded shadow-sm h-100 card-hover">
                        <div class="mb-3">
                            <i class="fas fa-people-carry feature-icon"></i>
                        </div>
                        <h4 class="fw-bold">Desarrollo Social</h4>
                        <p class="text-muted">Genera empleo y oportunidades en zonas rurales, fortaleciendo economías locales y el bienestar comunitario.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="fw-bold huinagro-text-primary mb-4">Sobre HuinAgro</h2>
                    <p class="lead">Sistema especializado en la gestión de procesos acuícolas.</p>
                    <p>Optimizamos el control de producción, registro de capturas y análisis de datos para empresas del sector acuícola.</p>
                    
                </div>
                <div class="col-lg-6">
                    <img src="img\Acuicultura.jpg" alt="Acuicultura" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    
    <footer style="background-color: var(--huinagro-dark);" class="text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="fw-bold">HuinAgro</h5>
                    <p>Sistema de registro de venta de cultivo acuicola.</p>
                    <div class="social-icons">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="fw-bold">Enlaces</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white text-decoration-none">Inicio</a></li>
                        
                        <li><a href="#" class="text-white text-decoration-none">Contacto</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="fw-bold">Contacto</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2"></i> Bogotá, Colombia</li>
                        <li><i class="fas fa-phone me-2"></i> +57 123 456 7890</li>
                        <li><i class="fas fa-envelope me-2"></i> info@huinagro.com</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> HuinAgro. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
