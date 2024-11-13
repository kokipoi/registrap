<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'db.php';
$database = new Database();
$db = $database->getConnection();

// Obtener la acción de la URL (si existe)
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Identificar el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Obtener usuario específico
            $id = $_GET['id'];
            $query = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
            $query->bindParam(':id', $id);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } else {
            // Obtener todos los usuarios
            $query = $db->prepare("SELECT * FROM usuarios");
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        switch ($action) {
            case 'login':
                // Login
                $query = $db->prepare("SELECT * FROM usuarios WHERE email = :email AND password = :password");
                $query->bindParam(':email', $data->email);
                $query->bindParam(':password', $data->password);
                $query->execute();
                
                if ($user = $query->fetch(PDO::FETCH_ASSOC)) {
                    $token = bin2hex(random_bytes(16));
                    echo json_encode([
                        "success" => true,
                        "user" => $user,
                        "token" => $token
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(["success" => false, "message" => "Credenciales inválidas"]);
                }
                break;

            case 'register':
                // Registro
                $query = $db->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)");
                $query->bindParam(':nombre', $data->nombre);
                $query->bindParam(':email', $data->email);
                $query->bindParam(':password', $data->password);
                $query->bindParam(':rol', $data->rol);
                
                if ($query->execute()) {
                    $userId = $db->lastInsertId();
                    $token = bin2hex(random_bytes(16));
                    
                    $user = [
                        "id" => $userId,
                        "nombre" => $data->nombre,
                        "email" => $data->email,
                        "rol" => $data->rol
                    ];
                    
                    echo json_encode([
                        "success" => true,
                        "user" => $user,
                        "token" => $token
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "Error al registrar usuario"]);
                }
                break;

            case 'change-password':
                // Cambio de contraseña
                $query = $db->prepare("UPDATE usuarios SET password = :newPassword WHERE email = :email AND password = :oldPassword");
                $query->bindParam(':email', $data->email);
                $query->bindParam(':oldPassword', $data->oldPassword);
                $query->bindParam(':newPassword', $data->newPassword);
                
                if ($query->execute() && $query->rowCount() > 0) {
                    echo json_encode(["success" => true, "message" => "Contraseña actualizada"]);
                } else {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "Error al actualizar contraseña"]);
                }
                break;

            case 'check-email':
                // Verificar si el correo electrónico existe
                $query = $db->prepare("SELECT COUNT(*) as count FROM usuarios WHERE email = :email");
                $query->bindParam(':email', $data->email);
                $query->execute();
                $result = $query->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode(["exists" => $result['count'] > 0]);
                break;

            case 'reset-password':
                // Restablecer la contraseña
                $query = $db->prepare("UPDATE usuarios SET password = :newPassword WHERE email = :email");
                $query->bindParam(':email', $data->email);
                $query->bindParam(':newPassword', $data->newPassword);
                
                if ($query->execute() && $query->rowCount() > 0) {
                    echo json_encode(["success" => true, "message" => "Contraseña restablecida"]);
                } else {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "Error al restablecer la contraseña"]);
                }
                break;

            default:
                // Crear nuevo usuario (cuando no hay acción específica)
                $query = $db->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)");
                $query->bindParam(':nombre', $data->nombre);
                $query->bindParam(':email', $data->email);
                $query->bindParam(':password', $data->password);
                $query->bindParam(':rol', $data->rol);
                
                if ($query->execute()) {
                    echo json_encode(["success" => true, "message" => "Usuario creado"]);
                } else {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "Error al crear usuario"]);
                }
                break;
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $query = $db->prepare("UPDATE usuarios SET nombre = :nombre, email = :email, password = :password, rol = :rol WHERE id = :id");
        $query->bindParam(':nombre', $data->nombre);
        $query->bindParam(':email', $data->email);
        $query->bindParam(':password', $data->password);
        $query->bindParam(':rol', $data->rol);
        $query->bindParam(':id', $data->id);
        
        if ($query->execute()) {
            echo json_encode(["success" => true, "message" => "Usuario actualizado"]);
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Error al actualizar usuario"]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        $query = $db->prepare("DELETE FROM usuarios WHERE id = :id");
        $query->bindParam(':id', $data->id);
        
        if ($query->execute()) {
            echo json_encode(["success" => true, "message" => "Usuario eliminado"]);
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Error al eliminar usuario"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido"]);
        break;
}
?>