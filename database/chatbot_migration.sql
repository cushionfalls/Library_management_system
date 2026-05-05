-- ============================================================
-- Paper Library — ChatBot Migration
-- Run this SQL manually against your database before using
-- the LibBot chatbot feature.
-- ============================================================

-- 1. ChatLogs — Audit trail for every chatbot interaction
CREATE TABLE IF NOT EXISTS `ChatLogs` (
    `id`            INT(11)      NOT NULL AUTO_INCREMENT,
    `user_id`       INT(11)      NOT NULL,
    `message`       TEXT         NOT NULL        COMMENT 'User message (sanitised)',
    `reply`         TEXT         NOT NULL        COMMENT 'LLM response (raw text)',
    `model_used`    VARCHAR(100) NOT NULL        COMMENT 'e.g. llama-3.3-70b-versatile or gemini-2.5-flash',
    `tokens_used`   INT(11)      DEFAULT NULL    COMMENT 'Total tokens if returned by API',
    `response_ms`   INT(11)      DEFAULT NULL    COMMENT 'Round-trip time in milliseconds',
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_chatlogs_user` (`user_id`),
    KEY `idx_chatlogs_created` (`created_at`),
    CONSTRAINT `chatlogs_ibfk_1` FOREIGN KEY (`user_id`)
        REFERENCES `Users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. ChatRateLimits — Per-user hourly message counter
CREATE TABLE IF NOT EXISTS `ChatRateLimits` (
    `id`            INT(11)      NOT NULL AUTO_INCREMENT,
    `user_id`       INT(11)      NOT NULL,
    `message_count` INT(11)      NOT NULL DEFAULT 1,
    `window_start`  DATETIME     NOT NULL        COMMENT 'Start of the current 1-hour window',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_ratelimit_user_window` (`user_id`, `window_start`),
    CONSTRAINT `chatratelimits_ibfk_1` FOREIGN KEY (`user_id`)
        REFERENCES `Users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
