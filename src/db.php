<?php
declare(strict_types=1);

// MOD
final class DB
{
    private const FILE = __DIR__ . '/../db/quiz.sqlite';
    private static ?\PDO $pdo = null;

    public static function conn(): \PDO
    {
        if (self::$pdo === null) {
            $isNew = !file_exists(self::FILE);
            self::$pdo = new \PDO('sqlite:' . self::FILE);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            if ($isNew) {
                self::migrate();
                self::seed();
            }
        }
        return self::$pdo;
    }

    private static function migrate(): void
    {
        $sql = <<<SQL
CREATE TABLE quizzes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT
);
CREATE TABLE questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    quiz_id INTEGER NOT NULL,
    question_text TEXT NOT NULL,
    option_a TEXT,
    option_b TEXT,
    option_c TEXT,
    option_d TEXT,
    correct_option CHAR(1) NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,   -- nuevo
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
);
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL
);
CREATE TABLE attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    quiz_id INTEGER NOT NULL,
    score INTEGER NOT NULL,
    total INTEGER NOT NULL,
    taken_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
);
SQL;
        self::$pdo->exec($sql);
    }

    private static function seed(): void
    {
        $qz = self::$pdo->prepare('INSERT INTO quizzes (title, description) VALUES (?, ?)');
        $qz->execute(['Examen de PHP Básico', 'Pon a prueba tus conocimientos de los fundamentos de PHP.']);
        $quizId = (int) self::$pdo->lastInsertId();

        $qs = self::$pdo->prepare(
            'INSERT INTO questions
             (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $qs->execute([$quizId, '¿Qué significa PHP?', 'Página de inicio personal', 'PHP: Procesador de hipertexto', 'Procesador de hipervínculos privados', 'Página de enlace PHP', 'B']);
        $qs->execute([$quizId, '¿Cuál de los siguientes NO es un tipo de dato de PHP?', 'Entero', 'Booleano', 'Caracter', 'Flotante', 'C']);
        $qs->execute([$quizId, '¿Cuál es el resultado de echo "Hola" . " " . "Mundo";?', 'HelloWorld', 'Hola Mundo', 'Hello World', '"Hola Mundo"', 'B']);
    }
}
// END MOD