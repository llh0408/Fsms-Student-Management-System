-- Initialize fsms schema based on provided dump (assignments/challenges/students/teachers)
CREATE DATABASE IF NOT EXISTS fsms;
USE fsms;

CREATE TABLE IF NOT EXISTS assignments (
    `name` TEXT NOT NULL,
    `de` TEXT NOT NULL,
    `exp` DATE NOT NULL,
    `upload` TEXT NOT NULL,
    `submiss` TEXT NOT NULL,
    `teacher` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS challenges (
    `code` TEXT NOT NULL,
    `title` TEXT NOT NULL,
    `hint` TEXT NOT NULL,
    `upload` TEXT NOT NULL,
    `exp` DATE NOT NULL,
    `created_by` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS students (
    `fullname` TEXT NOT NULL,
    `name` TEXT NOT NULL,
    `avatar` TEXT NOT NULL,
    `pass` TEXT NOT NULL,
    `email` TEXT NOT NULL,
    `phone` TEXT NOT NULL,
    `history` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS teachers (
    `fullname` TEXT NOT NULL,
    `name` TEXT NOT NULL,
    `avatar` TEXT NOT NULL,
    `phone` TEXT NOT NULL,
    `email` TEXT NOT NULL,
    `pass` TEXT NOT NULL,
    `history` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

COMMIT;
