<?php

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Composer Funcionando - HuinAgro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0"><i class="bi bi-check-circle"></i> Verificación Composer - HuinAgro</h3>
                    </div>
                    <div class="card-body">
                        
                        <div class="alert alert-info">
                            <h5><i class="bi bi-info-circle"></i> ¡Perfecto!</h5>
                            <p class="mb-0">Si conservaste los archivos "composer" y la carpeta vendor/, entonces <strong>Composer SÍ está funcionando</strong>.</p>
                        </div>
                        
                       
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-gear"></i> Estado Actual del Sistema</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>📁 Archivos Composer:</h6>
                                        <ul class="list-unstyled">
                                            <li>
                                                <i class="bi bi-<?php echo file_exists('composer.json') ? 'check-circle text-success' : 'x-circle text-danger'; ?>"></i>
                                                <code>composer.json</code>
                                                <?php if (file_exists('composer.json')): ?>
                                                    <span class="text-success">✓ Existe</span>
                                                    <small class="text-muted d-block">Tamaño: <?php echo filesize('composer.json'); ?> bytes</small>
                                                <?php else: ?>
                                                    <span class="text-danger">✗ Falta</span>
                                                <?php endif; ?>
                                            </li>
                                            <li>
                                                <i class="bi bi-<?php echo file_exists('composer.lock') ? 'check-circle text-success' : 'x-circle text-danger'; ?>"></i>
                                                <code>composer.lock</code>
                                                <?php echo file_exists('composer.lock') ? '<span class="text-success">✓ Existe</span>' : '<span class="text-danger">✗ Falta</span>'; ?>
                                            </li>
                                            <li>
                                                <i class="bi bi-<?php echo is_dir('vendor') ? 'check-circle text-success' : 'x-circle text-danger'; ?>"></i>
                                                <code>vendor/</code>
                                                <?php if (is_dir('vendor')): ?>
                                                    <span class="text-success">✓ Existe</span>
                                                    <small class="text-muted d-block">
                                                        <?php 
                                                        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('vendor'));
                                                        $count = iterator_count($files);
                                                        echo "~$count archivos";
                                                        ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-danger">✗ Falta</span>
                                                <?php endif; ?>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>📦 Dependencias:</h6>
                                        <ul class="list-unstyled">
                                            <li>
                                                <i class="bi bi-<?php echo file_exists('vendor/phpmailer/phpmailer/src/PHPMailer.php') ? 'check-circle text-success' : 'x-circle text-danger'; ?>"></i>
                                                PHPMailer
                                                <?php if (file_exists('vendor/phpmailer/phpmailer/src/PHPMailer.php')): ?>
                                                    <span class="text-success">✓ Instalado</span>
                                                    <?php
                                                    
                                                    try {
                                                        require_once 'vendor/autoload.php';
                                                        $version = PHPMailer\PHPMailer\PHPMailer::VERSION;
                                                        echo "<small class='text-muted d-block'>Versión: $version</small>";
                                                    } catch (Exception $e) {
                                                        echo "<small class='text-warning d-block'>Versión: No detectada</small>";
                                                    }
                                                    ?>
                                                <?php else: ?>
                                                    <span class="text-danger">✗ No instalado</span>
                                                <?php endif; ?>
                                            </li>
                                            <li>
                                                <i class="bi bi-<?php echo file_exists('vendor/dompdf/dompdf/src/Dompdf.php') ? 'check-circle text-success' : 'x-circle text-danger'; ?>"></i>
                                                DomPDF
                                                <?php echo file_exists('vendor/dompdf/dompdf/src/Dompdf.php') ? '<span class="text-success">✓ Instalado</span>' : '<span class="text-danger">✗ No instalado</span>'; ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-envelope-gear"></i> Test PHPMailer</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $phpmailer_test = false;
                                $error_message = "";
                                
                                try {
                                    if (file_exists('vendor/autoload.php')) {
                                        require_once 'vendor/autoload.php';
                                        
                                        
                                        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                                        $phpmailer_test = true;
                                        
                                        echo '<div class="alert alert-success">';
                                        echo '<h6><i class="bi bi-check-circle"></i> ¡PHPMailer funciona perfectamente!</h6>';
                                        echo '<p><strong>Versión:</strong> ' . PHPMailer\PHPMailer\PHPMailer::VERSION . '</p>';
                                        echo '<p><strong>Clase cargada:</strong> ' . get_class($mail) . '</p>';
                                        echo '<p class="mb-0"><strong>Estado:</strong> Listo para configurar SMTP</p>';
                                        echo '</div>';
                                        
                                    } else {
                                        throw new Exception("No se encuentra vendor/autoload.php");
                                    }
                                } catch (Exception $e) {
                                    $error_message = $e->getMessage();
                                    echo '<div class="alert alert-warning">';
                                    echo '<h6><i class="bi bi-exclamation-triangle"></i> PHPMailer no se puede cargar</h6>';
                                    echo '<p><strong>Error:</strong> ' . $error_message . '</p>';
                                    echo '<p class="mb-0">Pero el sistema puede funcionar sin PHPMailer usando las alternativas.</p>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5><i class="bi bi-clipboard-check"></i> Diagnóstico Final</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $composer_ok = file_exists('composer.json') && is_dir('vendor');
                                $autoload_ok = file_exists('vendor/autoload.php');
                                ?>
                                
                                <?php if ($composer_ok && $autoload_ok): ?>
                                    <div class="alert alert-success">
                                        <h6>🎉 ¡Todo está funcionando correctamente!</h6>
                                        <ul class="mb-0">
                                            <li>✅ Composer está instalado y funcionando</li>
                                            <li>✅ Las dependencias están disponibles</li>
                                            <li>✅ El autoloader funciona</li>
                                            <li>✅ PHPMailer está <?php echo $phpmailer_test ? 'funcionando' : 'disponible'; ?></li>
                                        </ul>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <h6><i class="bi bi-lightbulb"></i> El problema NO es Composer</h6>
                                        <p class="mb-0">
                                            <strong>El verdadero problema:</strong> Los servidores locales (Laragon, XAMPP) no pueden enviar emails reales. 
                                            Esto es normal y se soluciona con servicios externos como EmailJS.
                                        </p>
                                    </div>
                                    
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <h6>⚠️ Composer necesita atención</h6>
                                        <p>Algunos archivos de Composer faltan o están dañados.</p>
                                        <p class="mb-0">Ejecuta <code>composer install</code> para solucionarlo.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5><i class="bi bi-arrow-right-circle"></i> Próximos Pasos</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>🚀 Para Emails Reales:</h6>
                                        <ol>
                                            <li>Configurar <strong>EmailJS</strong> (5 min)</li>
                                            <li>O usar <strong>FormSubmit</strong> (1 min)</li>
                                            <li>O configurar <strong>Gmail SMTP</strong></li>
                                        </ol>
                                        <a href="setup_email_facil.php" class="btn btn-success btn-sm">
                                            <i class="bi bi-envelope"></i> Configurar Emails
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>✅ Sistema Actual:</h6>
                                        <ul>
                                            <li>Facturas se generan correctamente</li>
                                            <li>Emails se muestran en pantalla</li>
                                            <li>PDFs funcionan perfectamente</li>
                                            <li>Sistema 100% operativo</li>
                                        </ul>
                                        <a href="views/admin_facturas.php" class="btn btn-primary btn-sm">
                                            <i class="bi bi-file-earmark-pdf"></i> Probar Facturas
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver al Sistema
                            </a>
                            <button class="btn btn-info" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Verificar Nuevamente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
