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

CREATE TABLE `WalletTransactions` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `amount` int NOT NULL,
  `type` ENUM ('CREDIT', 'DEBIT') NOT NULL,
  `reason` ENUM ('TOP_UP', 'BOOK_RENT', 'BOOK_BUY', 'REFUND', 'MEMBERSHIP') NOT NULL,
  `external_ref` varchar(255),
  `created_at` datetime NOT NULL DEFAULT (now())
);

CREATE TABLE `MembershipPlans` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `slug` varchar(40) UNIQUE NOT NULL,
  `name` varchar(255) NOT NULL,
  `duration_days` int NOT NULL,
  `price` int NOT NULL,
  `is_active` bool NOT NULL DEFAULT true,
  `created_at` datetime NOT NULL DEFAULT (now())
);

CREATE TABLE `UserMemberships` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `status` ENUM ('ACTIVE', 'CANCELLED', 'EXPIRED') NOT NULL DEFAULT 'ACTIVE',
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `updated_at` datetime
);

CREATE TABLE `MembershipPurchases` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `membership_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `amount` int NOT NULL,
  `wallet_transaction_id` int,
  `purchased_at` datetime NOT NULL DEFAULT (now())
);

CREATE TABLE `BookReviews` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `book_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` int NOT NULL,
  `review` text,
  `created_at` datetime NOT NULL DEFAULT (now())
);

CREATE TABLE `UserBookAccess` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `book_id` int NOT NULL,
  `access_type` ENUM ('OWNED', 'MEMBERSHIP') NOT NULL,
  `source_ref` int,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `updated_at` datetime
);

CREATE TABLE `UserBookProgress` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `book_id` int NOT NULL,
  `progress_percent` int NOT NULL DEFAULT 0,
  `current_location` text,
  `last_opened_at` datetime,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `updated_at` datetime
);

ALTER TABLE `Sessions` ADD FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

ALTER TABLE `BookAuthors` ADD FOREIGN KEY (`book_id`) REFERENCES `Books` (`id`);

ALTER TABLE `BookAuthors` ADD FOREIGN KEY (`author_id`) REFERENCES `Authors` (`id`);

ALTER TABLE `BookTransactions` ADD FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

ALTER TABLE `BookTransactions` ADD FOREIGN KEY (`book_id`) REFERENCES `Books` (`id`);

ALTER TABLE `WalletTransactions` ADD FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

ALTER TABLE `UserMemberships` ADD FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);
ALTER TABLE `UserMemberships` ADD FOREIGN KEY (`plan_id`) REFERENCES `MembershipPlans` (`id`);
ALTER TABLE `MembershipPurchases` ADD FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);
ALTER TABLE `MembershipPurchases` ADD FOREIGN KEY (`membership_id`) REFERENCES `UserMemberships` (`id`);
ALTER TABLE `MembershipPurchases` ADD FOREIGN KEY (`plan_id`) REFERENCES `MembershipPlans` (`id`);
ALTER TABLE `MembershipPurchases` ADD FOREIGN KEY (`wallet_transaction_id`) REFERENCES `WalletTransactions` (`id`);

ALTER TABLE `BookReviews` ADD FOREIGN KEY (`book_id`) REFERENCES `Books` (`id`);

ALTER TABLE `BookReviews` ADD FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);
ALTER TABLE `UserBookAccess` ADD FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);
ALTER TABLE `UserBookAccess` ADD FOREIGN KEY (`book_id`) REFERENCES `Books` (`id`);
ALTER TABLE `UserBookProgress` ADD FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);
ALTER TABLE `UserBookProgress` ADD FOREIGN KEY (`book_id`) REFERENCES `Books` (`id`);

CREATE INDEX idx_otp_email_expires ON OTP(email, expires_at);
CREATE INDEX idx_users_email ON Users(email);
CREATE INDEX idx_books_genre ON Books(genre);
CREATE INDEX idx_transactions_user ON BookTransactions(user_id);
CREATE INDEX idx_transactions_due_date ON BookTransactions(due_date);
CREATE INDEX idx_reviews_book ON BookReviews(book_id);
CREATE UNIQUE INDEX uniq_user_book_access ON UserBookAccess(user_id, book_id);
CREATE UNIQUE INDEX uniq_user_book_progress ON UserBookProgress(user_id, book_id);

CREATE INDEX idx_memberships_user ON UserMemberships(user_id, ends_at);
CREATE INDEX idx_membership_purchases_user ON MembershipPurchases(user_id, purchased_at);

INSERT INTO MembershipPlans (slug, name, duration_days, price, is_active) VALUES
('MONTHLY_1', 'Bibliophile (1 Month)', 30, 4.99, 1),
('MONTHS_6', 'Bibliophile (6 Months)', 180, 15.99, 1),
('MONTHS_12', 'Bibliophile (12 Months)', 365, 30.00, 1);

