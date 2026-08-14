-- LearnHub Database Schema & Seed Data
-- Database: `learnhub`

CREATE DATABASE IF NOT EXISTS `learnhub` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `learnhub`;

-- 1. Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL DEFAULT 'Administrator',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Admin (username: admin, password: admin123)
INSERT INTO `admins` (`username`, `password_hash`, `name`) VALUES
('admin', '$2y$10$VXZ3soLXauxDRLqQNlFd.eLoY7wGaXZc7jm73pyz3q/fSPsQrl4h.', 'Ashutosh Raj (Admin)')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- 2. Subjects Table
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` VARCHAR(20) PRIMARY KEY,
  `short_code` VARCHAR(20) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `order_num` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Subjects
INSERT INTO `subjects` (`id`, `short_code`, `name`, `order_num`) VALUES
('os', 'OS', 'Operating System', 1),
('de', 'DE', 'Digital Electronics', 2),
('dsa', 'DSA', 'Data Structure & Algorithm', 3),
('dmgt', 'DM & GT', 'Discrete Mathematics & Graph Theory', 4),
('java', 'Java', 'Object Oriented Programming with Java', 5),
('uhv', 'UHV', 'Universal Human Values', 6)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 3. Units Table
CREATE TABLE IF NOT EXISTS `units` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `subject_id` VARCHAR(20) NOT NULL,
  `unit_number` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `notes_pdf_url` VARCHAR(255) DEFAULT '',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Units for all 6 subjects
INSERT INTO `units` (`id`, `subject_id`, `unit_number`, `title`, `notes_pdf_url`) VALUES
-- OS Units
(1, 'os', 1, 'Introduction', 'pdfs/notes/os-unit-1.pdf'),
(2, 'os', 2, 'Processes,Threads & Process Scheduling', 'pdfs/notes/os-unit-2.pdf'),
(3, 'os', 3, 'Interprocess Communication', 'pdfs/notes/os-unit-3.pdf'),
(4, 'os', 4, 'Deadlock', 'pdfs/notes/os-unit-4.pdf'),
(5, 'os', 5, 'Memory Management & Virtual Memory', 'pdfs/notes/os-unit-5.pdf'),
(6, 'os', 6, 'File Managemet,Disk Management & I/O Hardware', 'pdfs/notes/os-unit-6.pdf'),
-- DE Units
(7, 'de', 1, 'Fundamentals of digital system and logic families', 'pdfs/notes/de-unit-1.pdf'),
(8, 'de', 2, 'Combinational Digital Circuits', 'pdfs/notes/de-unit-2.pdf'),
(9, 'de', 3, 'Sequential Circuits & System', 'pdfs/notes/de-unit-3.pdf'),
(10, 'de', 4, 'A/D & D/A Converter', 'pdfs/notes/de-unit-4.pdf'),
(11, 'de', 5, 'Semiconductor memories', 'pdfs/notes/de-unit-5.pdf'),
(12, 'de', 6, 'Programmable Logic Devices', 'pdfs/notes/de-unit-6.pdf'),
-- DSA Units
(13, 'dsa', 1, 'Introduction', 'pdfs/notes/dsa-unit-1.pdf'),
(14, 'dsa', 2, 'Stack and Queue', 'pdfs/notes/dsa-unit-2.pdf'),
(15, 'dsa', 3, 'Linked List', 'pdfs/notes/dsa-unit-3.pdf'),
(16, 'dsa', 4, 'Searching,Sorting and Hashing', 'pdfs/notes/dsa-unit-4.pdf'),
(17, 'dsa', 5, 'Trees', 'pdfs/notes/dsa-unit-5.pdf'),
(18, 'dsa', 6, 'Graph', 'pdfs/notes/dsa-unit-6.pdf'),
-- DMGT Units
(19, 'dmgt', 1, 'Set Relation & Functions', 'pdfs/notes/dmgt-unit-1.pdf'),
(20, 'dmgt', 2, 'Principles of Mathematical Induction & Basic Counting Techniques', 'pdfs/notes/dmgt-unit-2.pdf'),
(21, 'dmgt', 3, 'Propositional Logic', 'pdfs/notes/dmgt-unit-3.pdf'),
(22, 'dmgt', 4, 'Proof Techniques', 'pdfs/notes/dmgt-unit-4.pdf'),
(23, 'dmgt', 5, 'Algebraic structures and Morphism', 'pdfs/notes/dmgt-unit-5.pdf'),
(24, 'dmgt', 6, 'Graphs and Trees', 'pdfs/notes/dmgt-unit-6.pdf'),
-- Java Units
(25, 'java', 1, 'OOP\'s Concepts & Java Programming', 'pdfs/notes/java-unit-1.pdf'),
(26, 'java', 2, 'Objects,Classes and constructors in Java', 'pdfs/notes/java-unit-2.pdf'),
(27, 'java', 3, 'Inheritance,Interfaces and Packages', 'pdfs/notes/java-unit-3.pdf'),
(28, 'java', 4, 'Exception Handling', 'pdfs/notes/java-unit-4.pdf'),
(29, 'java', 5, 'Introduction to Multithrading', 'pdfs/notes/java-unit-5.pdf'),
(30, 'java', 6, 'Files,The Collection framework and Connecting to database', 'pdfs/notes/java-unit-6.pdf'),
-- UHV Units
(31, 'uhv', 1, 'Introduction to value education', 'pdfs/notes/uhv-unit-1.pdf'),
(32, 'uhv', 2, 'Harmony in the Human being ', 'pdfs/notes/uhv-unit-2.pdf'),
(33, 'uhv', 3, 'Harmony in the family and society', 'pdfs/notes/uhv-unit-3.pdf'),
(34, 'uhv', 4, 'Harmony in the nature and existence', 'pdfs/notes/uhv-unit-4.pdf'),
(35, 'uhv', 5, 'Implications of the holistic understandings-a look at professionals', 'pdfs/notes/uhv-unit-5.pdf'),
(36, 'uhv', 6, 'Last Unit', 'pdfs/notes/uhv-unit-6.pdf')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `notes_pdf_url`=VALUES(`notes_pdf_url`);

-- 4. Lectures Table
CREATE TABLE IF NOT EXISTS `lectures` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `unit_id` INT NOT NULL,
  `lecture_title` VARCHAR(255) NOT NULL,
  `youtube_url` VARCHAR(500) NOT NULL,
  `order_num` INT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default 2 lectures for each unit
INSERT INTO `lectures` (`unit_id`, `lecture_title`, `youtube_url`, `order_num`)
SELECT u.id, CONCAT(u.title, ' — Introduction'), 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1 FROM `units` u
ON DUPLICATE KEY UPDATE `order_num`=VALUES(`order_num`);

INSERT INTO `lectures` (`unit_id`, `lecture_title`, `youtube_url`, `order_num`)
SELECT u.id, CONCAT(u.title, ' — Complete Concepts'), 'https://www.youtube.com/embed/dQw4w9WgXcQ', 2 FROM `units` u
ON DUPLICATE KEY UPDATE `order_num`=VALUES(`order_num`);

-- 5. Practicals Table
CREATE TABLE IF NOT EXISTS `practicals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `subject_name` VARCHAR(150) NOT NULL,
  `language` VARCHAR(50) NOT NULL,
  `type` ENUM('code', 'pdf', 'contact') NOT NULL DEFAULT 'code',
  `order_num` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `practicals` (`id`, `subject_name`, `language`, `type`, `order_num`) VALUES
(1, 'Data Structure & Algorithms', 'DSA Lab', 'code', 1),
(2, 'OOP\'s Using JAVA Lab', 'Java', 'code', 2),
(3, 'Operating System Lab', 'OS', 'code', 3),
(4, 'Digital Electronics Lab', 'DE LAB', 'pdf', 4),
(5, 'ForInternship 1', 'Contact us', 'contact', 5)
ON DUPLICATE KEY UPDATE `subject_name`=VALUES(`subject_name`), `type`=VALUES(`type`);

-- 6. Practical Experiments Table
CREATE TABLE IF NOT EXISTS `practical_experiments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `practical_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `pdf_url` VARCHAR(255) DEFAULT '',
  `code_content` TEXT DEFAULT NULL,
  `order_num` INT DEFAULT 1,
  FOREIGN KEY (`practical_id`) REFERENCES `practicals`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed DSA Experiments (28 experiments)
INSERT INTO `practical_experiments` (`practical_id`, `title`, `pdf_url`, `code_content`, `order_num`) VALUES
(1, 'Experiment 1', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 1: Array Insertion & Deletion\\n");\n    return 0;\n}', 1),
(1, 'Experiment 2', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 2: Linear and Binary Search\\n");\n    return 0;\n}', 2),
(1, 'Experiment 3', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 3: Stack implementation using Array\\n");\n    return 0;\n}', 3),
(1, 'Experiment 4', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 4: Infix to Postfix Conversion\\n");\n    return 0;\n}', 4),
(1, 'Experiment 5', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 5: Evaluation of Postfix Expression\\n");\n    return 0;\n}', 5),
(1, 'Experiment 6', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 6: Queue implementation using Array\\n");\n    return 0;\n}', 6),
(1, 'Experiment 7', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 7: Circular Queue Implementation\\n");\n    return 0;\n}', 7),
(1, 'Experiment 8', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 8: Singly Linked List Operations\\n");\n    return 0;\n}', 8),
(1, 'Experiment 9(a)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 9(a): Doubly Linked List\\n");\n    return 0;\n}', 9),
(1, 'Experiment 9(b)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 9(b): Circular Linked List\\n");\n    return 0;\n}', 10),
(1, 'Experiment 9(c)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 9(c): Polynomial Addition using Linked List\\n");\n    return 0;\n}', 11),
(1, 'Experiment 10(a)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 10(a): Binary Tree Traversals\\n");\n    return 0;\n}', 12),
(1, 'Experiment 10(b)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 10(b): Binary Search Tree Operations\\n");\n    return 0;\n}', 13),
(1, 'Experiment 11(a)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 11(a): Bubble Sort\\n");\n    return 0;\n}', 14),
(1, 'Experiment 11(b)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 11(b): Selection Sort\\n");\n    return 0;\n}', 15),
(1, 'Experiment 11(c)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 11(c): Insertion Sort\\n");\n    return 0;\n}', 16),
(1, 'Experiment 11(d)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 11(d): Quick Sort\\n");\n    return 0;\n}', 17),
(1, 'Experiment 12(a)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 12(a): Merge Sort\\n");\n    return 0;\n}', 18),
(1, 'Experiment 12(b)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 12(b): Heap Sort\\n");\n    return 0;\n}', 19),
(1, 'Experiment 12(c)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 12(c): Radix Sort\\n");\n    return 0;\n}', 20),
(1, 'Experiment 12(d)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 12(d): Shell Sort\\n");\n    return 0;\n}', 21),
(1, 'Experiment 13(a)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 13(a): Breadth First Search (BFS)\\n");\n    return 0;\n}', 22),
(1, 'Experiment 13(b)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 13(b): Depth First Search (DFS)\\n");\n    return 0;\n}', 23),
(1, 'Experiment 13(c)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 13(c): Dijkstra Algorithm\\n");\n    return 0;\n}', 24),
(1, 'Experiment 14', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 14: Hash Table with Linear Probing\\n");\n    return 0;\n}', 25),
(1, 'Experiment 15(a)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 15(a): Prim\'s Minimum Spanning Tree\\n");\n    return 0;\n}', 26),
(1, 'Experiment 15(b)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 15(b): Kruskal\'s MST Algorithm\\n");\n    return 0;\n}', 27),
(1, 'Experiment 15(c)', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 15(c): AVL Tree Insertion\\n");\n    return 0;\n}', 28);

-- Seed Java Lab Experiments (10 experiments)
INSERT INTO `practical_experiments` (`practical_id`, `title`, `pdf_url`, `code_content`, `order_num`) VALUES
(2, 'Experiment 1', '', 'class Experiment1 {\n    public static void main(String[] args) {\n        System.out.println("Experiment 1: Basic Java Syntax & Classes");\n    }\n}', 1),
(2, 'Experiment 2', '', 'class Experiment2 {\n    public static void main(String[] args) {\n        System.out.println("Experiment 2: Constructor Overloading in Java");\n    }\n}', 2),
(2, 'Experiment 3', '', 'class Experiment3 {\n    public static void main(String[] args) {\n        System.out.println("Experiment 3: Method Overloading & Overriding");\n    }\n}', 3),
(2, 'Experiment 4', '', 'class Experiment4 {\n    public static void main(String[] args) {\n        System.out.println("Experiment 4: Single & Multilevel Inheritance");\n    }\n}', 4),
(2, 'Experiment 5', '', 'class Experiment5 {\n    public static void main(String[] args) {\n        System.out.println("Experiment 5: Interfaces and Multiple Inheritance");\n    }\n}', 5),
(2, 'Experiment 6', '', 'class Experiment6 {\n    public static void main(String[] args) {\n        System.out.println("Experiment 6: Packages & Access Modifiers");\n    }\n}', 6),
(2, 'Experiment 7', '', 'class Experiment7 {\n    public static void main(String[] args) {\n        System.out.println("Experiment 7: Exception Handling (try-catch-finally)");\n    }\n}', 7),
(2, 'Experiment 8', '', 'class Experiment8 {\n    public static void main(String[] args) {\n        System.out.println("Experiment 8: Multithreading in Java (Thread class & Runnable)");\n    }\n}', 8),
(2, 'Experiment 9', '', 'class Experiment9 {\n    public static void main(String[] args) {\n        System.out.println("Experiment 9: File I/O Operations in Java");\n    }\n}', 9),
(2, 'Experiment 10', '', 'class Experiment10 {\n    public static void main(String[] args) {\n        System.out.println("Experiment 10: JDBC Database Connectivity");\n    }\n}', 10);

-- Seed OS Lab Experiments (10 experiments)
INSERT INTO `practical_experiments` (`practical_id`, `title`, `pdf_url`, `code_content`, `order_num`) VALUES
(3, 'Experiment 1', '', '#include <stdio.h>\n#include <unistd.h>\n\nint main() {\n    printf("Experiment 1: Process Creation using fork()\\n");\n    return 0;\n}', 1),
(3, 'Experiment 2', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 2: FCFS CPU Scheduling Algorithm\\n");\n    return 0;\n}', 2),
(3, 'Experiment 3', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 3: SJF (Shortest Job First) Scheduling\\n");\n    return 0;\n}', 3),
(3, 'Experiment 4', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 4: Round Robin CPU Scheduling\\n");\n    return 0;\n}', 4),
(3, 'Experiment 5', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 5: Priority Scheduling Algorithm\\n");\n    return 0;\n}', 5),
(3, 'Experiment 6', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 6: Producer-Consumer Problem using Semaphores\\n");\n    return 0;\n}', 6),
(3, 'Experiment 7', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 7: Dining Philosophers Problem\\n");\n    return 0;\n}', 7),
(3, 'Experiment 8', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 8: Banker\'s Algorithm for Deadlock Avoidance\\n");\n    return 0;\n}', 8),
(3, 'Experiment 9', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 9: FIFO Page Replacement Algorithm\\n");\n    return 0;\n}', 9),
(3, 'Experiment 10', '', '#include <stdio.h>\n\nint main() {\n    printf("Experiment 10: LRU Page Replacement Algorithm\\n");\n    return 0;\n}', 10);

-- Seed Digital Electronics Lab Experiments (11 PDF experiments)
INSERT INTO `practical_experiments` (`practical_id`, `title`, `pdf_url`, `code_content`, `order_num`) VALUES
(4, 'Experiment 1', 'pdfs/digital-electronics-experiment-1.pdf', '', 1),
(4, 'Experiment 2', 'pdfs/digital-electronics-experiment-2.pdf', '', 2),
(4, 'Experiment 3', 'pdfs/digital-electronics-experiment-3.pdf', '', 3),
(4, 'Experiment 4', 'pdfs/digital-electronics-experiment-4.pdf', '', 4),
(4, 'Experiment 5', 'pdfs/digital-electronics-experiment-5.pdf', '', 5),
(4, 'Experiment 6', 'pdfs/digital-electronics-experiment-6.pdf', '', 6),
(4, 'Experiment 7', 'pdfs/digital-electronics-experiment-7.pdf', '', 7),
(4, 'Experiment 8', 'pdfs/digital-electronics-experiment-8.pdf', '', 8),
(4, 'Experiment 9', 'pdfs/digital-electronics-experiment-9.pdf', '', 9),
(4, 'Experiment 10', 'pdfs/digital-electronics-experiment-10.pdf', '', 10),
(4, 'Experiment 11', 'pdfs/digital-electronics-experiment-11.pdf', '', 11);

-- 7. PYQs Table
CREATE TABLE IF NOT EXISTS `pyqs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `subject_id` VARCHAR(20) NOT NULL,
  `year` INT NOT NULL,
  `pdf_url` VARCHAR(255) NOT NULL,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed PYQs for all subjects (2021 - 2026)
INSERT INTO `pyqs` (`subject_id`, `year`, `pdf_url`) VALUES
('os', 2021, 'pdfs/pyqs/os-2021.pdf'),
('os', 2022, 'pdfs/pyqs/os-2022.pdf'),
('os', 2023, 'pdfs/pyqs/os-2023.pdf'),
('os', 2024, 'pdfs/pyqs/os-2024.pdf'),
('os', 2025, 'pdfs/pyqs/os-2025.pdf'),
('os', 2026, 'pdfs/pyqs/os-2026.pdf'),
('de', 2021, 'pdfs/pyqs/de-2021.pdf'),
('de', 2022, 'pdfs/pyqs/de-2022.pdf'),
('de', 2023, 'pdfs/pyqs/de-2023.pdf'),
('de', 2024, 'pdfs/pyqs/de-2024.pdf'),
('de', 2025, 'pdfs/pyqs/de-2025.pdf'),
('de', 2026, 'pdfs/pyqs/de-2026.pdf'),
('dsa', 2021, 'pdfs/pyqs/dsa-2021.pdf'),
('dsa', 2022, 'pdfs/pyqs/dsa-2022.pdf'),
('dsa', 2023, 'pdfs/pyqs/dsa-2023.pdf'),
('dsa', 2024, 'pdfs/pyqs/dsa-2024.pdf'),
('dsa', 2025, 'pdfs/pyqs/dsa-2025.pdf'),
('dsa', 2026, 'pdfs/pyqs/dsa-2026.pdf'),
('dmgt', 2021, 'pdfs/pyqs/dmgt-2021.pdf'),
('dmgt', 2022, 'pdfs/pyqs/dmgt-2022.pdf'),
('dmgt', 2023, 'pdfs/pyqs/dmgt-2023.pdf'),
('dmgt', 2024, 'pdfs/pyqs/dmgt-2024.pdf'),
('dmgt', 2025, 'pdfs/pyqs/dmgt-2025.pdf'),
('dmgt', 2026, 'pdfs/pyqs/dmgt-2026.pdf'),
('java', 2021, 'pdfs/pyqs/java-2021.pdf'),
('java', 2022, 'pdfs/pyqs/java-2022.pdf'),
('java', 2023, 'pdfs/pyqs/java-2023.pdf'),
('java', 2024, 'pdfs/pyqs/java-2024.pdf'),
('java', 2025, 'pdfs/pyqs/java-2025.pdf'),
('java', 2026, 'pdfs/pyqs/java-2026.pdf'),
('uhv', 2021, 'pdfs/pyqs/uhv-2021.pdf'),
('uhv', 2022, 'pdfs/pyqs/uhv-2022.pdf'),
('uhv', 2023, 'pdfs/pyqs/uhv-2023.pdf'),
('uhv', 2024, 'pdfs/pyqs/uhv-2024.pdf'),
('uhv', 2025, 'pdfs/pyqs/uhv-2025.pdf'),
('uhv', 2026, 'pdfs/pyqs/uhv-2026.pdf');

-- 8. Test Series Table
CREATE TABLE IF NOT EXISTS `test_series` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `subject_id` VARCHAR(20) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `duration_minutes` INT DEFAULT 15,
  `total_marks` INT DEFAULT 10,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Test Series
INSERT INTO `test_series` (`id`, `subject_id`, `title`, `duration_minutes`, `total_marks`, `description`) VALUES
(1, 'os', 'Operating System Fundamentals Mock Test', 15, 10, 'Practice test covering process management, scheduling, and memory concepts.'),
(2, 'dsa', 'Data Structures & Algorithms Speed Quiz', 15, 10, 'Objective questions on Trees, Graphs, Stacks, and Sorting techniques.'),
(3, 'java', 'Java Core & OOP Concepts Assessment', 15, 10, 'Test your knowledge on Polymorphism, Interfaces, and Exception Handling.')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

-- 9. Test Questions Table
CREATE TABLE IF NOT EXISTS `test_questions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `test_series_id` INT NOT NULL,
  `question_text` TEXT NOT NULL,
  `option_a` TEXT NOT NULL,
  `option_b` TEXT NOT NULL,
  `option_c` TEXT NOT NULL,
  `option_d` TEXT NOT NULL,
  `correct_option` ENUM('A', 'B', 'C', 'D') NOT NULL,
  `explanation` TEXT DEFAULT NULL,
  FOREIGN KEY (`test_series_id`) REFERENCES `test_series`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Test Questions
INSERT INTO `test_questions` (`test_series_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `explanation`) VALUES
(1, 'Which scheduling algorithm is non-preemptive by default?', 'Round Robin', 'First-Come, First-Served (FCFS)', 'Shortest Remaining Time First', 'Priority Scheduling with preemption', 'B', 'FCFS always executes processes in arrival order until completion without preemption.'),
(1, 'What condition is NOT necessary for a deadlock to occur?', 'Mutual Exclusion', 'Hold and Wait', 'Preemption', 'Circular Wait', 'C', 'No-preemption is required for deadlock; active preemption actually prevents deadlocks.'),
(1, 'Which page replacement algorithm suffers from Belady\'s Anomaly?', 'Optimal Page Replacement', 'LRU (Least Recently Used)', 'FIFO (First-In, First-Out)', 'LFU (Least Frequently Used)', 'C', 'FIFO can cause more page faults when allocated more page frames (Belady\'s anomaly).'),
(2, 'What is the worst-case time complexity of Quick Sort?', 'O(n)', 'O(n log n)', 'O(n^2)', 'O(log n)', 'C', 'Quick Sort reaches O(n^2) when the chosen pivot consistently produces highly unbalanced partitions.'),
(2, 'Which data structure is used for Breadth First Search (BFS) of a graph?', 'Stack', 'Queue', 'Priority Queue', 'Tree', 'B', 'BFS visits vertices in FIFO order, making a Queue data structure the optimal choice.'),
(3, 'Which keyword is used to prevent method overriding in Java?', 'static', 'abstract', 'final', 'synchronized', 'C', 'Applying the final keyword to a method declaration prevents subclasses from overriding it.');

-- 10. Coding Contests Table
CREATE TABLE IF NOT EXISTS `coding_contests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `language` VARCHAR(50) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `duration_minutes` INT DEFAULT 20,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Coding Contests
INSERT INTO `coding_contests` (`id`, `language`, `title`, `duration_minutes`, `description`) VALUES
(1, 'Java', 'Java OOP & Logic Challenge', 20, 'Solve objective code-output and bug-finding questions in Java.'),
(2, 'C', 'C Pointers & Memory Management Quiz', 20, 'Test your mastery of C pointers, struct alignment, and memory layouts.'),
(3, 'Python', 'Python Data Science & Syntax Contest', 20, 'Challenge your Python knowledge with list comprehensions, slicing, and lambda logic.')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

-- 11. Contest Questions Table
CREATE TABLE IF NOT EXISTS `contest_questions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `contest_id` INT NOT NULL,
  `question_text` TEXT NOT NULL,
  `code_snippet` TEXT DEFAULT NULL,
  `option_a` TEXT NOT NULL,
  `option_b` TEXT NOT NULL,
  `option_c` TEXT NOT NULL,
  `option_d` TEXT NOT NULL,
  `correct_option` ENUM('A', 'B', 'C', 'D') NOT NULL,
  `explanation` TEXT DEFAULT NULL,
  FOREIGN KEY (`contest_id`) REFERENCES `coding_contests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Contest Questions
INSERT INTO `contest_questions` (`contest_id`, `question_text`, `code_snippet`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `explanation`) VALUES
(1, 'What will be the output of the following Java snippet?', 'public class Test {\n    public static void main(String[] args) {\n        int x = 5;\n        System.out.println(x++ + ++x);\n    }\n}', '10', '12', '11', 'Compilation Error', 'B', 'x++ evaluates to 5 and sets x to 6. Then ++x increments x to 7 and evaluates to 7. 5 + 7 = 12.'),
(2, 'What does the following C code print?', '#include <stdio.h>\nint main() {\n    int arr[] = {10, 20, 30};\n    int *ptr = arr;\n    printf("%d", *(ptr + 1));\n    return 0;\n}', '10', '20', '30', 'Garbage Value', 'B', 'ptr + 1 points to the second element in the array (arr[1]), which is 20.'),
(3, 'What is the output of this Python slice operation?', 'lst = [1, 2, 3, 4, 5]\nprint(lst[1:4:2])', '[2, 3, 4]', '[2, 4]', '[1, 3]', '[2, 5]', 'B', 'Slice [1:4:2] takes elements from index 1 up to index 3 with step 2: elements are lst[1]=2 and lst[3]=4.');
