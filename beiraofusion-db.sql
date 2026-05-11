-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema beirao_fusion
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema beirao_fusion
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `beirao_fusion` DEFAULT CHARACTER SET utf8mb4 ;
USE `beirao_fusion` ;

-- -----------------------------------------------------
-- Table `beirao_fusion`.`user_types`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`user_types` (
  `type_id` INT(11) NOT NULL AUTO_INCREMENT,
  `type_name` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`type_id`),
  UNIQUE INDEX `type_name` (`type_name` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`users` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `profile_image` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `ref_type_id` INT(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `username` (`username` ASC),
  UNIQUE INDEX `email` (`email` ASC),
  INDEX `fk_users_type_id` (`ref_type_id` ASC),
  CONSTRAINT `fk_users_type_id`
    FOREIGN KEY (`ref_type_id`)
    REFERENCES `beirao_fusion`.`user_types` (`type_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`difficulty`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`difficulty` (
  `difficulty_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`difficulty_id`))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`status` (
  `status_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`status_id`))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`category_recipe`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`category_recipe` (
  `category_recipe_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`category_recipe_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`recipes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`recipes` (
  `recipe_id` INT(11) NOT NULL AUTO_INCREMENT,
  `ref_user_id` INT(11) NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `prep_time` INT(11) NULL DEFAULT NULL,
  `image_url` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  `ref_difficulty_id` INT(11) NOT NULL DEFAULT 2,
  `ref_status_id` INT(11) NOT NULL DEFAULT 1,
  `ref_category_recipe_id` INT NOT NULL,
  PRIMARY KEY (`recipe_id`),
  INDEX `user_id` (`ref_user_id` ASC),
  INDEX `ref_difficulty_id` (`ref_difficulty_id` ASC),
  INDEX `ref_status_id` (`ref_status_id` ASC),
  INDEX `fk_recipes_category_recipe1_idx` (`ref_category_recipe_id` ASC),
  CONSTRAINT `recipes_ibfk_1`
    FOREIGN KEY (`ref_user_id`)
    REFERENCES `beirao_fusion`.`users` (`user_id`)
    ON DELETE CASCADE,
  CONSTRAINT `recipes_ibfk_3`
    FOREIGN KEY (`ref_difficulty_id`)
    REFERENCES `beirao_fusion`.`difficulty` (`difficulty_id`),
  CONSTRAINT `recipes_ibfk_4`
    FOREIGN KEY (`ref_status_id`)
    REFERENCES `beirao_fusion`.`status` (`status_id`),
  CONSTRAINT `fk_recipes_category_recipe1`
    FOREIGN KEY (`ref_category_recipe_id`)
    REFERENCES `beirao_fusion`.`category_recipe` (`category_recipe_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`comments`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`comments` (
  `comment_id` INT(11) NOT NULL AUTO_INCREMENT,
  `ref_user_id` INT(11) NOT NULL,
  `ref_recipe_id` INT(11) NOT NULL,
  `content` TEXT NOT NULL,
  `parent_comment_id` INT(11) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`comment_id`),
  INDEX `user_id` (`ref_user_id` ASC),
  INDEX `recipe_id` (`ref_recipe_id` ASC),
  INDEX `parent_comment_id` (`parent_comment_id` ASC),
  CONSTRAINT `comments_ibfk_1`
    FOREIGN KEY (`ref_user_id`)
    REFERENCES `beirao_fusion`.`users` (`user_id`)
    ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2`
    FOREIGN KEY (`ref_recipe_id`)
    REFERENCES `beirao_fusion`.`recipes` (`recipe_id`)
    ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_3`
    FOREIGN KEY (`parent_comment_id`)
    REFERENCES `beirao_fusion`.`comments` (`comment_id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`follows`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`follows` (
  `follower_id` INT(11) NOT NULL,
  `following_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`follower_id`, `following_id`),
  UNIQUE INDEX `unique_follow` (`follower_id` ASC, `following_id` ASC),
  INDEX `following_id` (`following_id` ASC),
  CONSTRAINT `follows_ibfk_1`
    FOREIGN KEY (`follower_id`)
    REFERENCES `beirao_fusion`.`users` (`user_id`)
    ON DELETE CASCADE,
  CONSTRAINT `follows_ibfk_2`
    FOREIGN KEY (`following_id`)
    REFERENCES `beirao_fusion`.`users` (`user_id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`category_ingredients`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`category_ingredients` (
  `category_ingredients_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`category_ingredients_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`ingredients`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`ingredients` (
  `ingredient_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `ref_category_ingredients_id` INT NOT NULL,
  PRIMARY KEY (`ingredient_id`),
  UNIQUE INDEX `name` (`name` ASC),
  INDEX `fk_ingredients_category_ingredients1_idx` (`ref_category_ingredients_id` ASC),
  CONSTRAINT `fk_ingredients_category_ingredients1`
    FOREIGN KEY (`ref_category_ingredients_id`)
    REFERENCES `beirao_fusion`.`category_ingredients` (`category_ingredients_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`recipe_ingredients`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`recipe_ingredients` (
  `ref_recipe_id` INT(11) NOT NULL,
  `ref_ingredient_id` INT(11) NOT NULL,
  `quantity` DECIMAL(8,2) NULL DEFAULT NULL,
  `unit` VARCHAR(5) NULL DEFAULT NULL,
  UNIQUE INDEX `unique_recipe_ingredient` (`ref_recipe_id` ASC, `ref_ingredient_id` ASC),
  INDEX `ingredient_id` (`ref_ingredient_id` ASC),
  CONSTRAINT `recipe_ingredients_ibfk_1`
    FOREIGN KEY (`ref_recipe_id`)
    REFERENCES `beirao_fusion`.`recipes` (`recipe_id`)
    ON DELETE CASCADE,
  CONSTRAINT `recipe_ingredients_ibfk_2`
    FOREIGN KEY (`ref_ingredient_id`)
    REFERENCES `beirao_fusion`.`ingredients` (`ingredient_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`recipe_likes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`recipe_likes` (
  `ref_user_id` INT(11) NOT NULL,
  `ref_recipe_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`ref_user_id`, `ref_recipe_id`),
  INDEX `ref_recipe_id` (`ref_recipe_id` ASC),
  CONSTRAINT `recipe_likes_ibfk_1`
    FOREIGN KEY (`ref_user_id`)
    REFERENCES `beirao_fusion`.`users` (`user_id`),
  CONSTRAINT `recipe_likes_ibfk_2`
    FOREIGN KEY (`ref_recipe_id`)
    REFERENCES `beirao_fusion`.`recipes` (`recipe_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`recipe_saves`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`recipe_saves` (
  `ref_user_id` INT(11) NOT NULL,
  `ref_recipe_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`ref_user_id`, `ref_recipe_id`),
  INDEX `ref_recipe_id` (`ref_recipe_id` ASC),
  CONSTRAINT `recipe_saves_ibfk_1`
    FOREIGN KEY (`ref_user_id`)
    REFERENCES `beirao_fusion`.`users` (`user_id`),
  CONSTRAINT `recipe_saves_ibfk_2`
    FOREIGN KEY (`ref_recipe_id`)
    REFERENCES `beirao_fusion`.`recipes` (`recipe_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`recipe_steps`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`recipe_steps` (
  `step_id` INT(11) NOT NULL AUTO_INCREMENT,
  `ref_recipe_id` INT(11) NOT NULL,
  `step_number` INT(11) NOT NULL,
  `description` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`step_id`),
  INDEX `ref_recipe_id` (`ref_recipe_id` ASC),
  CONSTRAINT `recipe_steps_ibfk_1`
    FOREIGN KEY (`ref_recipe_id`)
    REFERENCES `beirao_fusion`.`recipes` (`recipe_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`vouchers_type`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`vouchers_type` (
  `vouchers_type_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`vouchers_type_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`vouchers`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`vouchers` (
  `vouchers_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `ref_vouchers_type_id` INT NOT NULL,
  PRIMARY KEY (`vouchers_id`),
  INDEX `fk_vouchers_vouchers_type1_idx` (`ref_vouchers_type_id` ASC),
  CONSTRAINT `fk_vouchers_vouchers_type1`
    FOREIGN KEY (`ref_vouchers_type_id`)
    REFERENCES `beirao_fusion`.`vouchers_type` (`vouchers_type_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `beirao_fusion`.`vouchers_users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beirao_fusion`.`vouchers_users` (
  `ref_vouchers_id` INT NOT NULL,
  `ref_user_id` INT(11) NOT NULL,
  PRIMARY KEY (`ref_vouchers_id`, `ref_user_id`),
  INDEX `fk_vouchers_has_users_users1_idx` (`ref_user_id` ASC),
  INDEX `fk_vouchers_has_users_vouchers1_idx` (`ref_vouchers_id` ASC),
  CONSTRAINT `fk_vouchers_has_users_vouchers1`
    FOREIGN KEY (`ref_vouchers_id`)
    REFERENCES `beirao_fusion`.`vouchers` (`vouchers_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_vouchers_has_users_users1`
    FOREIGN KEY (`ref_user_id`)
    REFERENCES `beirao_fusion`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
