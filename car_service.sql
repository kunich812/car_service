-- phpMyAdmin SQL Dump
-- version 5.1.3-3.red80
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Май 07 2026 г., 04:07
-- Версия сервера: 10.11.11-MariaDB
-- Версия PHP: 8.1.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `car_service`
--

-- --------------------------------------------------------

--
-- Структура таблицы `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `type` enum('individual','company') NOT NULL DEFAULT 'individual',
  `company_name` varchar(150) DEFAULT NULL,
  `inn` varchar(12) DEFAULT NULL,
  `car_plate` varchar(15) NOT NULL,
  `car_vin` varchar(17) DEFAULT NULL,
  `car_brand` varchar(50) NOT NULL,
  `car_model` varchar(50) NOT NULL,
  `car_year` int(11) DEFAULT NULL,
  `car_color` varchar(30) DEFAULT NULL,
  `car_mileage` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `clients`
--

INSERT INTO `clients` (`id`, `full_name`, `phone`, `email`, `address`, `type`, `company_name`, `inn`, `car_plate`, `car_vin`, `car_brand`, `car_model`, `car_year`, `car_color`, `car_mileage`, `created_at`, `updated_at`) VALUES
(1, 'Иванов Иван Иванович', '+7(999)123-45-67', 'ivanov@mail.ru', 'г. Москва, ул. Ленина, д. 1, кв. 10', 'individual', NULL, NULL, 'А123ВС177', 'JTNBE40K603122345', 'Toyota', 'Camry', 2020, 'Белый', 45000, '2026-05-07 11:02:25', '2026-05-07 11:02:25'),
(2, 'Петров Петр Петрович', '+7(999)765-43-21', 'petrov@mail.ru', 'г. Москва, ул. Мира, д. 5, кв. 22', 'individual', NULL, NULL, 'В456КМ77', 'ZFA22300000000001', 'Kia', 'Rio', 2022, 'Красный', 12000, '2026-05-07 11:02:25', '2026-05-07 11:02:25'),
(3, 'ООО ТрансЛогистик', '+7(495)111-22-33', 'info@translog.ru', 'г. Москва, ул. Транспортная, д. 10', 'company', 'ООО ТрансЛогистик', '7723987654', 'С789УХ199', 'XTA21100000000002', 'Lada', 'Vesta', 2023, 'Серый', 8000, '2026-05-07 11:02:25', '2026-05-07 11:02:25');

-- --------------------------------------------------------

--
-- Структура таблицы `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `role` enum('admin','manager','senior_manager','storekeeper','accountant','mechanic') NOT NULL,
  `login` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `hire_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `employees`
--

INSERT INTO `employees` (`id`, `full_name`, `role`, `login`, `password_hash`, `hire_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Иванов Иван Иванович', 'manager', 'manager', '12345678', '2026-01-10', 1, '2026-05-07 11:02:25', '2026-05-07 11:02:25'),
(2, 'Петров Петр Петрович', 'storekeeper', 'storekeeper', '12345678', '2026-01-15', 1, '2026-05-07 11:02:25', '2026-05-07 11:02:25'),
(3, 'Сидоров Алексей Петрович', 'admin', 'admin', 'admin123', '2026-01-01', 1, '2026-05-07 11:02:25', '2026-05-07 11:02:25'),
(4, 'Смирнова Елена Викторовна', 'accountant', 'accountant', '12345678', '2026-02-01', 1, '2026-05-07 11:02:25', '2026-05-07 11:02:25'),
(5, 'Механик Сергей Викторович', 'mechanic', 'mechanic', '12345678', '2026-01-20', 1, '2026-05-07 11:02:25', '2026-05-07 11:02:25');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `manager_id` int(11) NOT NULL,
  `mechanic_id` int(11) DEFAULT NULL,
  `status` enum('accepted','in_progress','waiting_parts','completed','delivered','closed') NOT NULL DEFAULT 'accepted',
  `discount_percent` decimal(5,2) DEFAULT 0.00,
  `total_services` decimal(10,2) DEFAULT 0.00,
  `total_parts` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) GENERATED ALWAYS AS (`total_services` + `total_parts`) STORED,
  `discount_amount` decimal(10,2) GENERATED ALWAYS AS (`total_amount` * `discount_percent` / 100) STORED,
  `final_amount` decimal(10,2) GENERATED ALWAYS AS (`total_amount` - `discount_amount`) STORED,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `client_id`, `manager_id`, `mechanic_id`, `status`, `discount_percent`, `total_services`, `total_parts`, `notes`, `created_at`, `completed_at`, `updated_at`) VALUES
(1, 1, 1, 5, 'completed', '0.00', '3500.00', '4000.00', 'Плановое ТО', '2026-05-05 10:00:00', '2026-05-05 14:30:00', '2026-05-07 11:02:26'),
(2, 2, 1, NULL, 'closed', '5.00', '2500.00', '0.00', '', '2026-05-04 09:00:00', '2026-05-04 16:00:00', '2026-05-07 11:02:26'),
(3, 3, 1, 5, 'in_progress', '0.00', '3000.00', '4500.00', 'Срочный ремонт', '2026-05-03 11:00:00', NULL, '2026-05-07 11:02:26');

-- --------------------------------------------------------

--
-- Структура таблицы `order_history`
--

CREATE TABLE `order_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(30) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `changed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `order_history`
--

INSERT INTO `order_history` (`id`, `order_id`, `status`, `changed_by`, `changed_at`) VALUES
(1, 1, 'Принят', 1, '2026-05-05 10:00:00'),
(2, 1, 'В работе', 5, '2026-05-05 10:15:00'),
(3, 1, 'Выполнен', 1, '2026-05-05 14:30:00'),
(4, 2, 'Принят', 1, '2026-05-04 09:00:00'),
(5, 2, 'Закрыт', 1, '2026-05-04 16:00:00'),
(6, 3, 'Принят', 1, '2026-05-03 11:00:00'),
(7, 3, 'В работе', 5, '2026-05-03 11:30:00');

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_type` enum('service','part') NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `part_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) GENERATED ALWAYS AS (`quantity` * `unit_price`) STORED,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_type`, `service_id`, `part_id`, `quantity`, `unit_price`, `created_at`) VALUES
(1, 1, 'service', 1, NULL, '0.50', '2000.00', '2026-05-07 11:02:26'),
(2, 1, 'service', 2, NULL, '1.00', '2500.00', '2026-05-07 11:02:26'),
(3, 1, 'part', NULL, 1, '1.00', '3200.00', '2026-05-07 11:02:26'),
(4, 1, 'part', NULL, 2, '1.00', '800.00', '2026-05-07 11:02:26'),
(5, 2, 'service', 2, NULL, '1.00', '2500.00', '2026-05-07 11:02:26'),
(6, 3, 'service', 3, NULL, '1.50', '2000.00', '2026-05-07 11:02:26'),
(7, 3, 'part', NULL, 3, '1.00', '4500.00', '2026-05-07 11:02:26');

-- --------------------------------------------------------

--
-- Структура таблицы `parts`
--

CREATE TABLE `parts` (
  `id` int(11) NOT NULL,
  `article` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category` enum('filters','oils','brakes','belts','spark_plugs') NOT NULL,
  `unit` enum('pcs','liters','kg','set','meters') NOT NULL DEFAULT 'pcs',
  `purchase_price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `stock_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `storage_location` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `parts`
--

INSERT INTO `parts` (`id`, `article`, `name`, `category`, `unit`, `purchase_price`, `sale_price`, `stock_quantity`, `min_stock`, `storage_location`, `created_at`, `updated_at`) VALUES
(1, 'MO-5W30-4L', 'Масло моторное 5W30 (4л)', 'oils', 'liters', '2500.00', '3200.00', '14.00', '3.00', 'Стеллаж А-1', '2026-05-07 11:02:25', '2026-05-07 11:02:26'),
(2, 'FO-15208', 'Фильтр масляный', 'filters', 'pcs', '500.00', '800.00', '15.00', '3.00', 'Стеллаж Б-2', '2026-05-07 11:02:25', '2026-05-07 11:02:25'),
(3, 'BR-001', 'Колодки тормозные передние', 'brakes', 'set', '3000.00', '4500.00', '2.00', '3.00', 'Стеллаж В-1', '2026-05-07 11:02:25', '2026-05-07 11:02:25'),
(4, 'FO-AIR-K', 'Фильтр воздушный', 'filters', 'pcs', '800.00', '1200.00', '8.00', '5.00', 'Стеллаж Б-3', '2026-05-07 11:02:25', '2026-05-07 11:02:25');

-- --------------------------------------------------------

--
-- Структура таблицы `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `method` enum('cash','card','bank_transfer') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('paid','partial','unpaid') NOT NULL DEFAULT 'unpaid',
  `payment_date` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `method`, `amount`, `status`, `payment_date`, `created_at`) VALUES
(1, 1, 'card', '7500.00', 'paid', '2026-05-05 14:35:00', '2026-05-07 11:02:26'),
(2, 2, 'cash', '2375.00', 'paid', '2026-05-04 16:05:00', '2026-05-07 11:02:26');

-- --------------------------------------------------------

--
-- Структура таблицы `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` enum('diagnostics','maintenance','tires','body_repair','electrical','brakes') NOT NULL,
  `standard_hours` decimal(5,2) NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) GENERATED ALWAYS AS (`standard_hours` * `hourly_rate`) STORED,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `services`
--

INSERT INTO `services` (`id`, `name`, `category`, `standard_hours`, `hourly_rate`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Замена масла ДВС', 'maintenance', '0.50', '2000.00', 1, '2026-05-07 11:02:25', '2026-05-07 11:02:25'),
(2, 'Диагностика ходовой части', 'diagnostics', '1.00', '2500.00', 1, '2026-05-07 11:02:25', '2026-05-07 11:02:25'),
(3, 'Замена тормозных колодок', 'brakes', '1.50', '2000.00', 1, '2026-05-07 11:02:25', '2026-05-07 11:07:51'),
(4, 'Шиномонтаж (4 колеса)', 'tires', '1.00', '1800.00', 1, '2026-05-07 11:02:25', '2026-05-07 11:02:25'),
(5, 'Компьютерная диагностика', 'diagnostics', '0.50', '3000.00', 1, '2026-05-07 11:02:25', '2026-05-07 11:02:25');

-- --------------------------------------------------------

--
-- Структура таблицы `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `part_id` int(11) NOT NULL,
  `movement_type` enum('supply','write_off') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) GENERATED ALWAYS AS (`quantity` * `unit_price`) STORED,
  `supplier_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `document_number` varchar(50) DEFAULT NULL,
  `movement_date` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `part_id`, `movement_type`, `quantity`, `unit_price`, `supplier_id`, `order_id`, `document_number`, `movement_date`, `created_at`) VALUES
(1, 1, 'supply', '10.00', '2500.00', 1, NULL, 'ПН-2026-001', '2026-05-01', '2026-05-07 11:02:26'),
(2, 1, 'write_off', '1.00', '3200.00', NULL, 1, 'ТН-2026-1256', '2026-05-05', '2026-05-07 11:02:26');

--
-- Триггеры `stock_movements`
--
DELIMITER $$
CREATE TRIGGER `update_stock_after_movement` AFTER INSERT ON `stock_movements` FOR EACH ROW BEGIN
    IF NEW.movement_type = 'supply' THEN
        UPDATE parts SET stock_quantity = stock_quantity + NEW.quantity WHERE id = NEW.part_id;
    ELSEIF NEW.movement_type = 'write_off' THEN
        UPDATE parts SET stock_quantity = stock_quantity - NEW.quantity WHERE id = NEW.part_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `phone`, `email`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'ООО АвтоЗапчасть', 'Сидоров А.А.', '+7(495)555-00-01', 'autozap@mail.ru', 'г. Москва, ул. Складская, д. 1', 1, '2026-05-07 11:02:26', '2026-05-07 11:02:26'),
(2, 'ИП АвтоМир', 'Кузнецов В.В.', '+7(495)555-00-02', 'avtomir@mail.ru', 'г. Москва, ул. Промышленная, д. 5', 1, '2026-05-07 11:02:26', '2026-05-07 11:02:26');

-- --------------------------------------------------------

--
-- Дублирующая структура для представления `v_orders_full`
-- (См. Ниже фактическое представление)
--
CREATE TABLE `v_orders_full` (
`order_id` int(11)
,`client` varchar(150)
,`car_plate` varchar(15)
,`car_brand` varchar(50)
,`car_model` varchar(50)
,`status` enum('accepted','in_progress','waiting_parts','completed','delivered','closed')
,`created_at` datetime
,`total_services` decimal(10,2)
,`total_parts` decimal(10,2)
,`total_amount` decimal(10,2)
,`discount_percent` decimal(5,2)
,`discount_amount` decimal(10,2)
,`final_amount` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Структура для представления `v_orders_full`
--
DROP TABLE IF EXISTS `v_orders_full`;

CREATE ALGORITHM=UNDEFINED DEFINER=`admin`@`localhost` SQL SECURITY DEFINER VIEW `v_orders_full`  AS SELECT `o`.`id` AS `order_id`, `c`.`full_name` AS `client`, `c`.`car_plate` AS `car_plate`, `c`.`car_brand` AS `car_brand`, `c`.`car_model` AS `car_model`, `o`.`status` AS `status`, `o`.`created_at` AS `created_at`, `o`.`total_services` AS `total_services`, `o`.`total_parts` AS `total_parts`, `o`.`total_amount` AS `total_amount`, `o`.`discount_percent` AS `discount_percent`, `o`.`discount_amount` AS `discount_amount`, `o`.`final_amount` AS `final_amount` FROM (`orders` `o` join `clients` `c` on(`o`.`client_id` = `c`.`id`))  ;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_name` (`full_name`),
  ADD KEY `idx_car_plate` (`car_plate`);

--
-- Индексы таблицы `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `manager_id` (`manager_id`),
  ADD KEY `mechanic_id` (`mechanic_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Индексы таблицы `order_history`
--
ALTER TABLE `order_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `part_id` (`part_id`);

--
-- Индексы таблицы `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `article` (`article`),
  ADD KEY `idx_stock` (`stock_quantity`);

--
-- Индексы таблицы `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_payment_date` (`payment_date`);

--
-- Индексы таблицы `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `part_id` (`part_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_movement_date` (`movement_date`);

--
-- Индексы таблицы `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `order_history`
--
ALTER TABLE `order_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `parts`
--
ALTER TABLE `parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_client_fk` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_manager_fk` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `orders_mechanic_fk` FOREIGN KEY (`mechanic_id`) REFERENCES `employees` (`id`);

--
-- Ограничения внешнего ключа таблицы `order_history`
--
ALTER TABLE `order_history`
  ADD CONSTRAINT `order_history_employee_fk` FOREIGN KEY (`changed_by`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `order_history_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_part_fk` FOREIGN KEY (`part_id`) REFERENCES `parts` (`id`),
  ADD CONSTRAINT `order_items_service_fk` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_movements_part_fk` FOREIGN KEY (`part_id`) REFERENCES `parts` (`id`),
  ADD CONSTRAINT `stock_movements_supplier_fk` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
