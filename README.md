<div align="center">
  <img src="public/img/logo_corona.jpg" alt="MC Barber Logo" width="150" />
  <h1>MC BARBER</h1>
  <p><strong>Barbería Premium - Sistema de Gestión Integral</strong></p>
  
  <p>
    <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version" />
    <img src="https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
    <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5" />
    <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3" />
  </p>
</div>

---

<p align="center">
  <strong>MC BARBER</strong> es una aplicación web integral diseñada para la administración eficiente y elegante de una barbería premium. Desarrollada con <strong>PHP nativo</strong> bajo el patrón <strong>MVC (Modelo-Vista-Controlador)</strong>, ofrece una experiencia de usuario fluida tanto para la administración del negocio como para la reserva de citas por parte de los clientes.
</p>

---

## 📑 Tabla de Contenidos

- [✨ Características Principales](#-características-principales)
- [👥 Roles del Sistema](#-roles-del-sistema)
- [📂 Estructura del Proyecto](#-estructura-del-proyecto)
- [⚙️ Requisitos Previos](#-requisitos-previos)
- [🚀 Instalación y Configuración](#-instalación-y-configuración)
- [💻 Uso del Sistema](#-uso-del-sistema)

---

## ✨ Características Principales

- 📅 **Gestión de Citas:** Reservas, reprogramaciones y cancelaciones en tiempo real.
- 🕒 **Gestor de Turnos / Cola:** Organización eficiente de clientes en sala de espera y en atención.
- 🔔 **Notificaciones:** Sistema automatizado para confirmar, cancelar y recordar citas.
- 🔐 **Autenticación Segura:** Acceso protegido, con soporte 2FA obligatorio para administradores.
- 📊 **Reportes y Auditoría:** Trazabilidad completa de acciones críticas e informes de rendimiento.
- 💈 **Gestión de Servicios:** Catálogo dinámico de categorías y servicios ofrecidos.

## 👥 Roles del Sistema

El sistema está optimizado para tres perfiles de usuario, garantizando que cada uno tenga acceso solo a las herramientas que necesita:

| Rol | Descripción y Permisos |
| :--- | :--- |
| 👑 **Administrador** | Control total. Gestión de usuarios (barberos y clientes), catálogo de servicios, reportes financieros, configuración global y auditoría de seguridad. |
| ✂️ **Barbero** | Gestión de su agenda diaria, disponibilidad, citas asignadas, control de la cola de turnos y visualización de sus propios reportes. |
| 👤 **Cliente** | Exploración de servicios, reserva de citas con su barbero favorito, revisión de su historial, y gestión (reprogramación/cancelación) de sus citas activas. |

## 📂 Estructura del Proyecto

El código fuente está organizado siguiendo las mejores prácticas del patrón **MVC**:

```text
📦 mc_barber
 ┣ 📂 config/         # ⚙️ Configuraciones globales (ej. database.php)
 ┣ 📂 controllers/    # 🎮 Controladores modulares (admin, auth, barbero, cliente)
 ┣ 📂 includes/       # 🧩 Componentes reutilizables y utilidades
 ┣ 📂 models/         # 🗄️ Modelos de interacción con la BD (Cita, Usuario, etc.)
 ┣ 📂 public/         # 🌐 Archivos públicos (Landing page, CSS, JS, img, uploads)
 ┣ 📂 sql/            # 📜 Scripts de estructuración de la BD
 ┗ 📂 views/          # 🖥️ Plantillas visuales divididas por módulo
```

## ⚙️ Requisitos Previos

Para ejecutar este proyecto en un entorno local, asegúrate de contar con:

- 🖥️ **Servidor Web:** Apache o Nginx (Laragon, XAMPP, MAMP recomendados).
- 🐘 **PHP:** Versión `7.4` o superior (Recomendado `8.x`).
- 🐬 **Base de Datos:** MySQL `5.7+` o MariaDB equivalente.

## 🚀 Instalación y Configuración

Sigue estos sencillos pasos para levantar el entorno de desarrollo:

1. **Clonar el Proyecto**
   > Ubica la carpeta `mc_barber` en el directorio público de tu servidor local (ej. `C:\laragon\www\` o `C:\xampp\htdocs\`).

2. **Preparar la Base de Datos**
   > Abre tu cliente SQL favorito (phpMyAdmin, DBeaver) y crea una base de datos vacía llamada `mc_barberdb`.
   > 
   > Importa el script SQL ubicado en `sql/Query #2.sql` para generar las tablas.

3. **Configurar Credenciales**
   > Edita el archivo `config/database.php` y ajusta los parámetros de conexión según tu entorno:
   
   ```php
   $host = '127.0.0.1';
   $user = 'root';        // Tu usuario local
   $password = '';        // Tu contraseña (déjalo vacío si no aplica)
   $db = 'mc_barberdb';   // Nombre de la base de datos
   $port = 3306;
   ```

4. **¡Todo Listo!**
   > Ingresa desde tu navegador a `http://localhost/mc_barber/public/` (o tu dominio virtual asignado) para comenzar a usar la aplicación.

## 💻 Uso del Sistema

- 🏠 **Landing Page:** Accediendo a `/public/` encontrarás una interfaz moderna presentando los servicios de la barbería.
- 🔑 **Acceso al Panel:** Inicia sesión con tus credenciales. El sistema te redirigirá automáticamente a la interfaz correspondiente a tu perfil.
- 📱 **Diseño Responsivo:** Las vistas y el menú lateral (sidebar) se adaptan dinámicamente tanto a dispositivos móviles como a los permisos de tu perfil.

---
<div align="center">
  Hecho con ❤️ para brindar la mejor experiencia en servicios de barbería.
</div>
