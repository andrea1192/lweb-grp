CREATE TABLE Users (
username	VARCHAR(160)	PRIMARY KEY,
password	VARCHAR(160)	NOT NULL,
name		VARCHAR(160),
address		VARCHAR(160),
mail_pri	VARCHAR(160),
mail_sec	VARCHAR(160),
reputation	INTEGER			NOT NULL DEFAULT 1,
privilege	INTEGER			NOT NULL DEFAULT 1,
CONSTRAINT priv_levels CHECK (privilege BETWEEN 0 AND 3)
)

---- Privilege levels:
-- 0: visitor/banned
-- 1: user
-- 2: mod
-- 3: admin
