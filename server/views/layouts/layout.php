<?php

class Layout
{
    private $usuario;
    private $pageTitle;
    private $baseUrl;
    private $currentPage;

    public function __construct($usuario, $pageTitle = '', $baseUrl = '/project/public', $currentPage = '')
    {
        $this->usuario = $usuario;
        $this->pageTitle = $pageTitle;
        $this->baseUrl = $baseUrl;
        $this->currentPage = $currentPage;
    }

    public function header()
    {
        return '
        <div class="header">
            <div class="header-left">
                <div class="logo">UTTECAM</div>
                <div class="page-title">' . htmlspecialchars($this->pageTitle) . '</div>
            </div>
            <div class="user-info">
                <div class="user-dropdown" id="userDropdown">
                    <button class="user-trigger" onclick="toggleUserDropdown()">
                        <div class="user-avatar">
                            ' . strtoupper(substr($this->usuario['nombre'], 0, 1)) . '
                        </div>
                        <span>' . htmlspecialchars($this->usuario['nombre']) . '</span>
                        <span class="dropdown-arrow"><i class="fas fa-chevron-down"></i></span>
                    </button>
                    
                    <div class="dropdown-menu">
                        <div class="dropdown-header">
                            <div class="dropdown-avatar">
                                ' . strtoupper(substr($this->usuario['nombre'], 0, 1)) . '
                            </div>
                            <div class="dropdown-name">' . htmlspecialchars($this->usuario['nombre']) . '</div>
                            <div class="dropdown-email">' . htmlspecialchars($this->usuario['correo']) . '</div>
                            <span class="dropdown-role">' . ucfirst($this->usuario['rol']) . '</span>
                        </div>
                        
                        <div class="dropdown-body">
                            <div class="dropdown-info">
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-user"></i> Nombre:</span>
                                    <span class="info-value">' . htmlspecialchars($this->usuario['nombre']) . '</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-envelope"></i> Correo:</span>
                                    <span class="info-value">' . htmlspecialchars($this->usuario['correo']) . '</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-tag"></i> Rol:</span>
                                    <span class="info-value">' . ucfirst($this->usuario['rol']) . '</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-id-card"></i> ID:</span>
                                    <span class="info-value">#' . str_pad($this->usuario['id'], 6, '0', STR_PAD_LEFT) . '</span>
                                </div>
                            </div>
                            
                            <form action="' . $this->baseUrl . '/logout" method="POST">
                                <button type="submit" class="btn-logout">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        function toggleUserDropdown() {
            const dropdown = document.getElementById("userDropdown");
            dropdown.classList.toggle("active");
        }
        
        // Cerrar dropdown al hacer clic fuera
        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("userDropdown");
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove("active");
            }
        });
        </script>';
    }

    public function nav()
    {
        $isAdmin = $this->usuario['rol'] === 'admin';

        return '
        <div class="nav">
            <ul>
                <li><a href="' . $this->baseUrl . '/dashboard" class="' . ($this->currentPage === 'dashboard' ? 'active' : '') . '">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a></li>
                <li><a href="' . $this->baseUrl . '/documentos" class="' . ($this->currentPage === 'documentos' ? 'active' : '') . '">
                    <i class="fas fa-file-alt"></i> Documentos
                </a></li>
                <li><a href="' . $this->baseUrl . '/notificaciones" class="' . ($this->currentPage === 'notificaciones' ? 'active' : '') . '">
                    <i class="fas fa-bell"></i> Notificaciones
                </a></li>
                ' . ($isAdmin ? '<li><a href="' . $this->baseUrl . '/usuarios" class="' . ($this->currentPage === 'usuarios' ? 'active' : '') . '">
                    <i class="fas fa-users"></i> Usuarios
                </a></li>' : '') . '
            </ul>
        </div>';
    }

    public function footer()
    {
        return '
        <div class="footer">
            © 2025 UTTECAM. Todos los derechos reservados.
        </div>';
    }

    public function renderComplete($content, $title = '', $additionalCSS = '', $additionalJS = '')
    {
        $pageTitle = !empty($title) ? $title : $this->pageTitle;

        return '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . htmlspecialchars($pageTitle) . ' - UTTECAM</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  background: #f8f9fa;
  min-height: 100vh;
}

/* Header Styles */
.header {
  background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
  padding: 1rem 2rem;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  animation: slideDown 0.5s ease-out;
  position: relative;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.logo {
  font-size: 1.8rem;
  font-weight: bold;
  font-style: italic;
  letter-spacing: 1px;
}

.page-title {
  font-size: 1.2rem;
  font-weight: 500;
  opacity: 0.9;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  position: relative;
}

.user-dropdown {
  position: relative;
}

.user-trigger {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.1);
  padding: 0.5rem 1rem;
  border-radius: 25px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  color: white;
  font-size: 0.95rem;
}

.user-trigger:hover {
  background: rgba(255, 255, 255, 0.2);
}

.user-avatar {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #ff8c42 0%, #ff7b2e 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.9rem;
}

.dropdown-arrow {
  transition: transform 0.3s ease;
}

.user-dropdown.active .dropdown-arrow {
  transform: rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  min-width: 280px;
  z-index: 1000;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.3s ease;
  margin-top: 0.5rem;
}

.user-dropdown.active .dropdown-menu {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e1e5e9;
  text-align: center;
}

.dropdown-avatar {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  font-size: 1.5rem;
  color: white;
  font-weight: bold;
}

.dropdown-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.25rem;
}

.dropdown-email {
  font-size: 0.9rem;
  color: #666;
}

.dropdown-role {
  background: linear-gradient(135deg, #ff8c42 0%, #ff7b2e 100%);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 15px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  margin-top: 0.5rem;
  display: inline-block;
}

.dropdown-body {
  padding: 1rem;
}

.dropdown-info {
  display: grid;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 8px;
  font-size: 0.9rem;
}

.info-label {
  font-weight: 600;
  color: #333;
}

.info-value {
  color: #666;
}

.btn-logout {
  width: 100%;
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
  color: white;
  border: none;
  padding: 0.75rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-logout:hover {
  background: linear-gradient(135deg, #ee5a52 0%, #dc4c64 100%);
  transform: translateY(-1px);
}

/* Navigation Styles */
.nav {
  background: white;
  padding: 0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  border-bottom: 3px solid #4a7c59;
  animation: slideDown 0.6s ease-out;
}

.nav ul {
  list-style: none;
  display: flex;
  max-width: 1200px;
  margin: 0 auto;
}

.nav li {
  flex: 1;
}

.nav a {
  display: block;
  text-decoration: none;
  color: #4a7c59;
  font-weight: 600;
  padding: 1rem 2rem;
  text-align: center;
  transition: all 0.3s ease;
  border-bottom: 3px solid transparent;
}

.nav a:hover,
.nav a.active {
  background: #f8f9fa;
  border-bottom-color: #ff8c42;
  color: #333;
}

/* Container Styles */
.container {
  max-width: 1200px;
  margin: 2rem auto;
  padding: 0 2rem;
  animation: fadeInUp 0.7s ease-out;
}

/* Card Styles */
.card {
  background: white;
  padding: 2rem;
  border-radius: 15px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
  border-top: 4px solid #4a7c59;
}

/* Form Styles */
.form-group {
  margin-bottom: 1.5rem;
  animation: fadeIn 0.8s ease-out;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #333;
  font-weight: 600;
}

.form-input,
.form-select,
.form-textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e1e5e9;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: #f8f9fa;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #4a7c59;
  background: white;
  box-shadow: 0 0 0 3px rgba(74, 124, 89, 0.1);
}

.form-textarea {
  min-height: 120px;
  resize: vertical;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

/* Button Styles */
.btn-primary {
  background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #5a8c69 0%, #6a9c79 100%);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(74, 124, 89, 0.3);
}

.btn-secondary {
  background: linear-gradient(135deg, #ff8c42 0%, #ff7b2e 100%);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: linear-gradient(135deg, #ff7b2e 0%, #ff6a1a 100%);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(255, 140, 66, 0.3);
}

.btn-submit {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 1rem;
}

.btn-submit:hover {
  background: linear-gradient(135deg, #5a8c69 0%, #6a9c79 100%);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(74, 124, 89, 0.3);
}

/* Message Styles */
.error-message,
.success-message {
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-weight: 500;
  animation: slideInDown 0.5s ease-out;
}

.error-message {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
  color: white;
}

.success-message {
  background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
  color: white;
}

/* Toast Styles */
.toast {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 1rem;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  z-index: 9999;
  animation: slideInRight 0.5s ease-out;
  max-width: 300px;
}

.toast-success {
  background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
}

.toast-error {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
}

/* Footer Styles */
.footer {
  background: #6c757d;
  color: white;
  text-align: center;
  padding: 1.5rem;
  margin-top: 3rem;
}

/* Animations */
@keyframes slideDown {
  from {
    transform: translateY(-100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes fadeInUp {
  from {
    transform: translateY(30px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideInDown {
  from {
    transform: translateY(-20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Responsive */
@media (max-width: 768px) {
  .header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .header-left {
    flex-direction: column;
    gap: 0.5rem;
  }

  .nav ul {
    flex-direction: column;
  }

  .container {
    padding: 0 1rem;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .dropdown-menu {
    right: -1rem;
    left: -1rem;
    min-width: auto;
  }
}

</style>

        </head>
        <body>
            ' . $this->header() . '
            ' . $this->nav() . '
            
            ' . $content . '
            
            ' . $this->footer() . '
            
            <script>
                // Función para mostrar notificaciones toast
                function showToast(message, type = "success") {
                    const toast = document.createElement("div");
                    toast.className = `toast toast-${type}`;
                    toast.textContent = message;
                    document.body.appendChild(toast);
                    
                    setTimeout(() => {
                        toast.style.opacity = "0";
                        setTimeout(() => {
                            document.body.removeChild(toast);
                        }, 500);
                    }, 3000);
                }
            </script>
            ' . $additionalJS . '
        </body>
        </html>';
    }
}
