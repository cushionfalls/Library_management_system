CREATE TABLE `Users` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` ENUM ('ADMIN', 'LIBRARIAN', 'USER') NOT NULL DEFAULT 'USER',
  `dob` datetime,
  `phone_number` varchar(15),
  `profile_image` varchar(500),
  `is_active` bool NOT NULL DEFAULT true,
  `is_verified` bool NOT NULL DEFAULT false,
  `verified_at` datetime,
  `wallet` int NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `updated_at` datetime
);

CREATE TABLE `Sessions` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `session_id` varchar(255) UNIQUE NOT NULL,
  `device_info` varchar(255),
  `created_at` datetime NOT NULL DEFAULT (now()),
  `valid_till` datetime NOT NULL
);

CREATE TABLE `OTP` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now())
);

CREATE TABLE `Authors` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `bio` text,
  `created_at` datetime NOT NULL DEFAULT (now())
);

CREATE TABLE `Books` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `isbn` varchar(13) UNIQUE NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `publisher` varchar(255),
  `published_at` datetime,
  `language` varchar(50) NOT NULL DEFAULT 'English',
  `genre` ENUM ('FANTASY', 'SCIENCE_FICTION', 'MYSTERY', 'ROMANCE', 'THRILLER', 'NON_FICTION', 'BIOGRAPHY', 'HISTORY', 'OTHERS') NOT NULL DEFAULT 'OTHERS',
  `number_of_copies` int NOT NULL DEFAULT 0,
  `price` int NOT NULL,
  `online_rent_price` int,
  `online_buy_price` int,
  `cover_image` varchar(500),
  `online_copy_pdf` varchar(500),
  `created_at` datetime NOT NULL DEFAULT (now()),
  `updated_at` datetime
);

CREATE TABLE `BookAuthors` (
  `book_id` int NOT NULL,
  `author_id` int NOT NULL
);

CREATE TABLE `BookTransactions` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `book_id` int NOT NULL,
  `user_id` int NOT NULL,
  `transaction_type` ENUM ('RENT', 'INHAND', 'ONLINE') NOT NULL DEFAULT 'INHAND',
  `amount_paid` int NOT NULL,
  `due_date` datetime,
  `returned_at` datetime,
  `is_returned` bool NOT NULL DEFAULT false,
  `created_at` datetime NOT NULL DEFAULT (now())
);

CREATE TABLE `Fines` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `book_transaction_id` int NOT NULL,
  `user_id` int NOT NULL,
  `amount` int NOT NULL,
  `is_paid` bool NOT NULL DEFAULT false,
  `paid_at` datetime,
  `created_at` datetime NOT NULL DEFAULT (now())
);

CREATE TABLE `WalletTransactions` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `amount` int NOT NULL,
  `type` ENUM ('CREDIT', 'DEBIT') NOT NULL,
  `reason` ENUM ('TOP_UP', 'BOOK_RENT', 'BOOK_BUY', 'FINE_PAYMENT', 'REFUND') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now())
);

CREATE TABLE `BookReviews` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `book_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` int NOT NULL,
  `review` text,
  `created_at` datetime NOT NULL DEFAULT (now())
);

ALTER TABLE `Sessions` ADD FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

ALTER TABLE `BookAuthors` ADD FOREIGN KEY (`book_id`) REFERENCES `Books` (`id`);

ALTER TABLE `BookAuthors` ADD FOREIGN KEY (`author_id`) REFERENCES `Authors` (`id`);

ALTER TABLE `BookTransactions` ADD FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

ALTER TABLE `BookTransactions` ADD FOREIGN KEY (`book_id`) REFERENCES `Books` (`id`);

ALTER TABLE `Fines` ADD FOREIGN KEY (`book_transaction_id`) REFERENCES `BookTransactions` (`id`);

ALTER TABLE `Fines` ADD FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

ALTER TABLE `WalletTransactions` ADD FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

ALTER TABLE `BookReviews` ADD FOREIGN KEY (`book_id`) REFERENCES `Books` (`id`);

ALTER TABLE `BookReviews` ADD FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

CREATE INDEX idx_otp_email_expires ON OTP(email, expires_at);
CREATE INDEX idx_users_email ON Users(email);
CREATE INDEX idx_books_genre ON Books(genre);
CREATE INDEX idx_transactions_user ON BookTransactions(user_id);
CREATE INDEX idx_transactions_due_date ON BookTransactions(due_date);
CREATE INDEX idx_fines_user ON Fines(user_id);
CREATE INDEX idx_reviews_book ON BookReviews(book_id);

