<?php
class Helpers {
    public static function sanitize(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    public static function sanitizeEmail(string $email): string {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    public static function validateRequired(array $data, array $fields): array {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
        }
        return $errors;
    }
    public static function formatDate(DateTime $date): string {
        return $date->format('Y-m-d H:i:s');
    }
    public static function formatDateShort(DateTime $date): string {
        return $date->format('Y-m-d');
    }
    public static function redirect(string $url): void {
        header("Location: $url");
        exit();
    }
    public static function flash(string $type, string $message): void {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type] = $message;
    }
    public static function getFlash(string $type): ?string {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }
    public static function getPost(string $key, $default = null) {
        return $_POST[$key] ?? $default;
    }
    public static function getGet(string $key, $default = null) {
        return $_GET[$key] ?? $default;
    }
    public static function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    public static function isGet(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    public static function generateCsrfToken(): string {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    public static function validateCsrfToken(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    public static function paginate(int $page, int $limit, int $total): array {
        $totalPages = ceil($total / $limit);
        $offset = ($page - 1) * $limit;
        return [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => $totalPages,
            'offset' => $offset,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ];
    }
    public static function getStatusBadge(string $status): string {
        $badges = [
            'a_faire' => '<span class="badge badge-secondary">To Do</span>',
            'en_cours' => '<span class="badge badge-primary">In Progress</span>',
            'termine' => '<span class="badge badge-success">Completed</span>',
            'planifie' => '<span class="badge badge-info">Planned</span>',
            'en_attente' => '<span class="badge badge-warning">Waiting</span>',
            'open' => '<span class="badge badge-danger">Open</span>',
            'resolved' => '<span class="badge badge-success">Resolved</span>'
        ];
        return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }
    public static function truncate(string $text, int $length = 50): string {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }
}
