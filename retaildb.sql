-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2026 at 09:36 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `retaildb`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateProductStock` (IN `p_id` INT, IN `qty_sold` INT)   BEGIN
    UPDATE Products 
    SET StockQuantity = StockQuantity - qty_sold 
    WHERE ProductID = p_id;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `CalculateTax` (`price` DECIMAL(10,2)) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    RETURN price * 0.12;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `CategoryID` int(11) NOT NULL,
  `CategoryName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`CategoryID`, `CategoryName`) VALUES
(1, 'Electronics'),
(2, 'Furniture'),
(3, 'Clothing'),
(4, 'Groceries'),
(5, 'Toys'),
(6, 'Books'),
(7, 'Beauty'),
(8, 'Automotive'),
(9, 'Sports'),
(10, 'Garden');

-- --------------------------------------------------------

--
-- Table structure for table `orderitems`
--

CREATE TABLE `orderitems` (
  `OrderItemID` int(11) NOT NULL,
  `OrderID` int(11) DEFAULT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderitems`
--

INSERT INTO `orderitems` (`OrderItemID`, `OrderID`, `ProductID`, `Quantity`) VALUES
(2, 2, 2, 2),
(3, 3, 3, 1),
(4, 4, 4, 2),
(5, 5, 5, 3),
(6, 6, 6, 1),
(7, 7, 7, 2),
(8, 8, 8, 1),
(9, 9, 9, 2),
(10, 10, 10, 2),
(13, 11, 1, 1);

--
-- Triggers `orderitems`
--
DELIMITER $$
CREATE TRIGGER `After_OrderItem_Delete` AFTER DELETE ON `orderitems` FOR EACH ROW BEGIN
    UPDATE Products 
    SET StockQuantity = StockQuantity + OLD.Quantity
    WHERE ProductID = OLD.ProductID;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `After_OrderItem_Insert` AFTER INSERT ON `orderitems` FOR EACH ROW BEGIN
    UPDATE Products 
    SET StockQuantity = StockQuantity - NEW.Quantity
    WHERE ProductID = NEW.ProductID;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `After_OrderItem_Update` AFTER UPDATE ON `orderitems` FOR EACH ROW BEGIN
    UPDATE Products 
    SET StockQuantity = StockQuantity + (OLD.Quantity - NEW.Quantity)
    WHERE ProductID = NEW.ProductID;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `OrderID` int(11) NOT NULL,
  `OrderDate` date DEFAULT NULL,
  `TotalAmount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`OrderID`, `OrderDate`, `TotalAmount`) VALUES
(1, '2024-01-01', 1200.00),
(2, '2024-01-02', 300.00),
(3, '2024-01-03', 25.00),
(4, '2024-01-04', 9.00),
(5, '2024-01-05', 47.97),
(6, '2024-01-06', 45.00),
(7, '2024-01-07', 70.00),
(8, '2024-01-08', 85.00),
(9, '2024-01-09', 40.00),
(10, '2024-01-10', 37.00),
(11, '2026-05-09', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ProductID` int(11) NOT NULL,
  `ProductName` varchar(100) DEFAULT NULL,
  `Price` decimal(10,2) DEFAULT NULL,
  `StockQuantity` int(11) DEFAULT NULL,
  `CategoryID` int(11) DEFAULT NULL,
  `SupplierID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ProductID`, `ProductName`, `Price`, `StockQuantity`, `CategoryID`, `SupplierID`) VALUES
(1, 'Laptop', 20000.00, 10, 1, 1),
(2, 'Office Chair', 2500.00, 20, 2, 2),
(3, 'Cotton T-Shirt', 500.00, 50, 3, 3),
(4, 'Organic Milk', 250.00, 100, 4, 4),
(5, 'Action Figure', 250.00, 30, 5, 5),
(6, 'SQL Guidebook', 45.00, 12, 6, 6),
(7, 'Face Serum', 100.00, 40, 7, 7),
(8, 'Brake Pads', 4000.00, 8, 8, 8),
(9, 'Yoga Mat', 300.00, 25, 9, 9),
(10, 'Garden Hoe', 100.00, 10, 10, 10);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `SupplierID` int(11) NOT NULL,
  `SupplierName` varchar(100) DEFAULT NULL,
  `ContactEmail` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`SupplierID`, `SupplierName`, `ContactEmail`) VALUES
(1, 'Watch Tech', 'contact@watchtech.com'),
(2, 'Home Depot Inc', 'sales@homedepot.com'),
(3, 'Fashion Hub', 'info@fashionhub.com'),
(4, 'Fresh Foods', 'supply@freshfoods.com'),
(5, 'Toy Kingdom', 'orders@toyking.com'),
(6, 'National Book Store', 'admin@books.com'),
(7, 'Glow Cosmetics', 'support@glow.com'),
(8, 'Car Parts Co', 'parts@carparts.com'),
(9, 'Adidas', 'brand@adidas.com'),
(10, 'Greens', 'plant@greens.com');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT 'admin@aretailsolutions.com',
  `Role` enum('Admin','Staff') DEFAULT 'Staff',
  `Status` enum('Active','Inactive') DEFAULT 'Active',
  `SecurityQuestion` varchar(255) DEFAULT 'What is your mother''s maiden name?',
  `SecurityAnswer` varchar(255) DEFAULT 'Belllo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Username`, `Password`, `FullName`, `Email`, `Role`, `Status`, `SecurityQuestion`, `SecurityAnswer`) VALUES
(1, 'admin', '1234', 'System Administrator', 'admin@aretailsolutions.com', 'Staff', 'Active', 'What is your mother\'s maiden name?', 'Bello'),
(8, 'justin', '123', 'justin alcayde', 'vincealcayde2@gmail.com', 'Staff', 'Active', 'What was your first pet\'s name?', 'brownie'),
(9, 'admin1', '123', 'admin', 'admin@gmail.com', 'Staff', 'Inactive', 'What was your first pet\'s name?', 'twinkle'),
(10, 'admin23', '123', 'admin2', 'admin2@gmail.com', 'Staff', 'Inactive', 'What was your first pet\'s name?', 'brownie'),
(11, 'testadmin_8377', 'password123', 'Test Admin', 'testadmin_8377@example.com', 'Staff', 'Active', 'What was your first pet\'s name?', 'Manila'),
(13, 'testadmin_1359', 'password123', 'Test Admin', 'testadmin_1359@example.com', 'Staff', 'Active', 'What was your first pet\'s name?', 'Manila'),
(14, 'AutomatedUser1', 'testpass', 'Automated Test User', 'auto@example.com', 'Staff', 'Active', 'What was your first pet\'s name?', 'Auto Answer');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_lowstock`
-- (See below for the actual view)
--
CREATE TABLE `view_lowstock` (
`ProductName` varchar(100)
,`StockQuantity` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_ordersummary`
-- (See below for the actual view)
--
CREATE TABLE `view_ordersummary` (
`OrderID` int(11)
,`OrderDate` date
,`TotalRevenue` decimal(42,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_productdetails`
-- (See below for the actual view)
--
CREATE TABLE `view_productdetails` (
`ProductName` varchar(100)
,`CategoryName` varchar(50)
,`SupplierName` varchar(100)
,`Price` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_salesrevenue`
-- (See below for the actual view)
--
CREATE TABLE `view_salesrevenue` (
`OrderID` int(11)
,`OrderDate` date
,`TotalRevenue` decimal(42,2)
);

-- --------------------------------------------------------

--
-- Structure for view `view_lowstock`
--
DROP TABLE IF EXISTS `view_lowstock`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_lowstock`  AS SELECT `products`.`ProductName` AS `ProductName`, `products`.`StockQuantity` AS `StockQuantity` FROM `products` WHERE `products`.`StockQuantity` < 15 ;

-- --------------------------------------------------------

--
-- Structure for view `view_ordersummary`
--
DROP TABLE IF EXISTS `view_ordersummary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_ordersummary`  AS SELECT `o`.`OrderID` AS `OrderID`, `o`.`OrderDate` AS `OrderDate`, sum(`p`.`Price` * `oi`.`Quantity`) AS `TotalRevenue` FROM ((`orders` `o` join `orderitems` `oi` on(`o`.`OrderID` = `oi`.`OrderID`)) join `products` `p` on(`oi`.`ProductID` = `p`.`ProductID`)) GROUP BY `o`.`OrderID` ;

-- --------------------------------------------------------

--
-- Structure for view `view_productdetails`
--
DROP TABLE IF EXISTS `view_productdetails`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_productdetails`  AS SELECT `p`.`ProductName` AS `ProductName`, `c`.`CategoryName` AS `CategoryName`, `s`.`SupplierName` AS `SupplierName`, `p`.`Price` AS `Price` FROM ((`products` `p` join `categories` `c` on(`p`.`CategoryID` = `c`.`CategoryID`)) join `suppliers` `s` on(`p`.`SupplierID` = `s`.`SupplierID`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_salesrevenue`
--
DROP TABLE IF EXISTS `view_salesrevenue`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_salesrevenue`  AS SELECT `o`.`OrderID` AS `OrderID`, `o`.`OrderDate` AS `OrderDate`, sum(`p`.`Price` * `oi`.`Quantity`) AS `TotalRevenue` FROM ((`orders` `o` join `orderitems` `oi` on(`o`.`OrderID` = `oi`.`OrderID`)) join `products` `p` on(`oi`.`ProductID` = `p`.`ProductID`)) GROUP BY `o`.`OrderID` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`CategoryID`);

--
-- Indexes for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD PRIMARY KEY (`OrderItemID`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `CategoryID` (`CategoryID`),
  ADD KEY `SupplierID` (`SupplierID`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`SupplierID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `CategoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orderitems`
--
ALTER TABLE `orderitems`
  MODIFY `OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `SupplierID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD CONSTRAINT `orderitems_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`),
  ADD CONSTRAINT `orderitems_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`CategoryID`) REFERENCES `categories` (`CategoryID`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`SupplierID`) REFERENCES `suppliers` (`SupplierID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
