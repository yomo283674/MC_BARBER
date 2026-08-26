<?php

require_once __DIR__ . '/../config/database.php';

class Usuario {

    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Verifica si un email ya existe en la base de datos.
     */
    public function emailExiste(string $email): bool {
        $stmt = $this->conn->prepare(
            "SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        $existe = $stmt->num_rows > 0;
        $stmt->close();
        return $existe;
    }

    /**
     * Registra un nuevo usuario con rol de Cliente (id_rol = 3).
     *
     * @param string $nombre    Nombre completo del usuario
     * @param string $email     Email unico
     * @param string $password  Contrasena en texto plano (se hashea aqui)
     * @param string $telefono  Telefono del usuario
     * @param int    $id_rol    ID del rol (3 = Cliente por defecto)
     * @return bool
     */
    public function registrar(string $nombre, string $email, string $password, string $telefono, int $id_rol = 3): bool {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->conn->prepare(
            "INSERT INTO usuarios (id_rol, nombre, email, telefono, password, estado)
            VALUES (?, ?, ?, ?, ?, 'ACTIVO')"
        );
        $stmt->bind_param('issss', $id_rol, $nombre, $email, $telefono, $hash);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Busca un usuario por email y retorna sus datos si existe.
     *
     * @param string $email
     * @return array|null  Datos del usuario o null si no existe
     */
    public function buscarPorEmail(string $email): ?array {
        $stmt = $this->conn->prepare(
            "SELECT u.id_usuario, u.nombre, u.email, u.password, u.estado, u.id_rol, r.nombre AS rol, u.foto_perfil
            FROM usuarios u
            INNER JOIN roles r ON u.id_rol = r.id_rol
            WHERE u.email = ?
            LIMIT 1"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();
        return $usuario ?: null;
    }

    /**
     * Actualiza el campo ultimo_acceso al momento actual.
     *
     * @param int $id_usuario
     * @return void
     */
    public function actualizarUltimoAcceso(int $id_usuario): void {
        $stmt = $this->conn->prepare(
            "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?"
        );
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Obtiene todos los barberos activos.
     *
     * @return array
     */
    public function obtenerBarberosActivos(): array {
        $stmt = $this->conn->prepare(
            "SELECT id_usuario, nombre, foto_perfil, especialidad FROM usuarios WHERE id_rol = 2 AND estado = 'ACTIVO' ORDER BY nombre ASC"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $barberos = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $barberos;
    }

    /**
     * Obtiene los datos de un usuario por su ID.
     */
    public function obtenerPorId(int $id_usuario): ?array {
        $stmt = $this->conn->prepare(
            "SELECT id_usuario, nombre, email, telefono, foto_perfil FROM usuarios WHERE id_usuario = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();
        return $usuario ?: null;
    }

    /**
     * Actualiza el perfil de un usuario.
     */
    public function actualizarPerfil(int $id_usuario, string $nombre, string $email, string $telefono, ?string $password = null, ?string $foto_perfil = null): bool {
        $query = "UPDATE usuarios SET nombre = ?, email = ?, telefono = ?";
        $types = "sss";
        $params = [$nombre, $email, $telefono];

        if ($password) {
            $query .= ", password = ?";
            $types .= "s";
            $params[] = password_hash($password, PASSWORD_BCRYPT);
        }

        if ($foto_perfil) {
            $query .= ", foto_perfil = ?";
            $types .= "s";
            $params[] = $foto_perfil;
        }

        $query .= " WHERE id_usuario = ?";
        $types .= "i";
        $params[] = $id_usuario;

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
}
