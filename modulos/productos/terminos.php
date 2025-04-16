<?php
define('ACCESO_PERMITIDO', true);
require_once 'includes/config.php';
require_once 'includes/funciones.php';

// Incluir header
$titulo = "Términos y Condiciones";
include 'includes/header.php';
?>

<div class="container">
    <div class="terminos-container">
        <div class="page-header">
            <h1>Términos y Condiciones</h1>
            <p>Última actualización: <?php echo date('d/m/Y'); ?></p>
        </div>
        
        <div class="terminos-content">
            <h2>1. Introducción</h2>
            <p>Bienvenido a InfoMaster. Estos Términos y Condiciones rigen el uso del sitio web infomaster.com.mx (el "Sitio") y los servicios ofrecidos a través del mismo. Al utilizar nuestro Sitio, usted acepta cumplir y quedar obligado por estos Términos y Condiciones. Si no está de acuerdo con alguna parte de estos términos, por favor no utilice nuestro Sitio.</p>
            
            <h2>2. Definiciones</h2>
            <p>"Nosotros", "nos", "nuestro" se refiere a InfoMaster, una empresa establecida en México.</p>
            <p>"Usuario", "usted", "su" se refiere a cualquier persona que acceda o utilice nuestro Sitio.</p>
            <p>"Productos digitales" se refiere a los bienes intangibles disponibles para su compra en nuestro Sitio.</p>
            <p>"Wallet" se refiere al sistema de saldo virtual disponible en nuestro Sitio para realizar compras.</p>
            
            <h2>3. Servicios Ofrecidos</h2>
            <p>InfoMaster ofrece un sistema de entrega automática de productos digitales mediante el uso de un sistema de wallet. Los usuarios pueden recargar su wallet y utilizar el saldo para adquirir productos digitales que se entregarán automáticamente tras la confirmación del pago.</p>
            
            <h2>4. Registro y Cuentas de Usuario</h2>
            <p>Para utilizar algunas funciones de nuestro Sitio, es necesario crear una cuenta. Al registrarse, usted acepta proporcionar información precisa y completa. Usted es el único responsable de mantener la confidencialidad de su contraseña y de todas las actividades que ocurran bajo su cuenta.</p>
            
            <h2>5. Recargas y Pagos</h2>
            <p>Todos los precios están en pesos mexicanos (MXN) e incluyen los impuestos aplicables. El usuario puede recargar su wallet mediante los métodos de pago disponibles en el Sitio. Una vez completada la recarga, el saldo estará disponible para su uso inmediato.</p>
            <p>Las recargas no son reembolsables excepto en circunstancias excepcionales y a la entera discreción de InfoMaster.</p>
            
            <h2>6. Entrega de Productos</h2>
            <p>Tras la confirmación del pago, los productos digitales se entregarán automáticamente a través del sistema. InfoMaster no garantiza que los productos digitales sean compatibles con todos los dispositivos o sistemas operativos.</p>
            
            <h2>7. Política de Devoluciones</h2>
            <p>Debido a la naturaleza de los productos digitales, las compras son generalmente finales y no reembolsables. Sin embargo, si un producto no funciona como se describe, el usuario puede reportar el problema dentro de los 7 días posteriores a la compra para una posible resolución o reembolso.</p>
            
            <h2>8. Derechos de Propiedad Intelectual</h2>
            <p>Todos los contenidos del Sitio, incluyendo pero no limitado a textos, gráficos, logotipos, íconos e imágenes, son propiedad de InfoMaster y están protegidos por las leyes de propiedad intelectual.</p>
            <p>Al comprar un producto digital, el usuario adquiere una licencia de uso personal y no transferible, no los derechos de propiedad intelectual sobre dicho producto.</p>
            
            <h2>9. Prohibiciones</h2>
            <p>Está prohibido usar el Sitio para actividades ilegales, dañinas, amenazantes, abusivas, difamatorias o de cualquier otra manera inadecuada. También está prohibido intentar acceder no autorizado a áreas protegidas del Sitio o interferir con el funcionamiento del mismo.</p>
            
            <h2>10. Limitación de Responsabilidad</h2>
            <p>InfoMaster no será responsable por daños indirectos, incidentales, especiales, punitivos o consecuentes que surjan de o en relación con el uso del Sitio o los productos adquiridos a través del mismo.</p>
            
            <h2>11. Modificaciones de los Términos</h2>
            <p>InfoMaster se reserva el derecho de modificar estos Términos y Condiciones en cualquier momento. Los cambios entrarán en vigor inmediatamente después de su publicación en el Sitio. El uso continuado del Sitio después de cualquier cambio constituye su aceptación de los términos modificados.</p>
            
            <h2>12. Ley Aplicable</h2>
            <p>Estos Términos y Condiciones se regirán e interpretarán de acuerdo con las leyes de México, sin tener en cuenta sus disposiciones sobre conflictos de leyes.</p>
            
            <h2>13. Contacto</h2>
            <p>Si tiene alguna pregunta sobre estos Términos y Condiciones, puede contactarnos a través de los métodos disponibles en nuestra página de contacto.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>