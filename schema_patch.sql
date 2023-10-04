RENAME TABLE subjects TO individuals;
ALTER TABLE evidence ADD COLUMN applicability ENUM('applicable','not-applicable','unclaimed') DEFAULT 'unclaimed' AFTER quality;