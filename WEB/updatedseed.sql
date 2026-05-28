USE pepperland_hotel;



INSERT IGNORE INTO room_status (status_name) VALUES
('Available'),
('Occupied'),
('Maintenance'),
('Reserved');

INSERT IGNORE INTO booking_status (status_name) VALUES
('Pending'),
('Confirmed'),
('Checked In'),
('Checked Out'),
('Cancelled'),
('No Show');

INSERT IGNORE INTO payment_methods (method_name) VALUES
('Credit Card'),
('Debit Card'),
('GCash'),
('Bank Transfer'),
('Cash');

INSERT IGNORE INTO payment_status (status_name) VALUES
('Pending'),
('Completed'),
('Failed'),
('Refunded'),
('Partially Refunded');

INSERT IGNORE INTO roles (role_name, description) VALUES
('Admin', 'Full system access and management privileges.'),
('Guest', 'Standard registered guest account.'),
('Staff', 'Hotel staff with limited operational access.');

INSERT IGNORE INTO hotel_profile
(
    hotel_name,
    description,
    star_rating,
    contact_email,
    contact_phone,
    website_url,
    street_address,
    city,
    province,
    country,
    postal_code,
    check_in_time,
    check_out_time
)
VALUES
(
    'The Pepperland Hotel',
    'A premium hotel offering world-class hospitality and comfort.',
    4.5,
    'info@pepperlandhotel.com',
    '+63-XXX-XXX-XXXX',
    'https://www.pepperlandhotel.com',
    '123 Pepperland Avenue',
    'Legazpi City',
    'Albay',
    'Philippines',
    '4500',
    '14:00:00',
    '12:00:00'
);





INSERT INTO room_types (type_name, base_price, description, max_capacity, bed_type) VALUES

('Classic Courtyard Room',
 1800.00,
 'A charming room overlooking the hotel courtyard, perfect for solo travellers or couples seeking a peaceful retreat at an affordable rate.',
 2, 'Queen Bed'),

('Standard Double Room',
 1800.00,
 'A comfortable room with essential amenities, ideal for solo travellers or couples on a budget looking for a clean, pleasant stay.',
 2, 'Queen Bed'),

('Standard Twin Room',
 1900.00,
 'Two single beds ideal for friends or colleagues travelling together, with all standard in-room conveniences.',
 2, 'Twin Beds'),

('Garden Terrace Room',
 2200.00,
 'A refreshing room with a private terrace overlooking the manicured hotel gardens, offering a tranquil escape.',
 2, 'Queen Bed'),

('Comfort Studio Suite',
 2500.00,
 'A cosy studio-style room combining sleeping and living areas, featuring a kitchenette alcove and upgraded bedding.',
 2, 'King Bed'),

('Deluxe Double Room',
 2800.00,
 'Spacious deluxe room with city or garden view, modern furnishings, and upgraded amenities including a mini bar.',
 2, 'King Bed'),

('Deluxe Twin Room',
 2900.00,
 'Generously sized twin room with contemporary décor, walk-in shower, and sweeping views of the hotel grounds.',
 2, 'Twin Beds'),

('Premium Queen Room',
 3200.00,
 'An elevated queen room with premium bedding, rainfall shower, in-room safe, and a dedicated workspace.',
 2, 'Queen Bed'),

('Signature Corner Room',
 3500.00,
 'Positioned at the building corner for dual-aspect windows, offering panoramic city and garden vistas with upscale furnishings.',
 2, 'King Bed'),

('Superior Queen Room',
 3800.00,
 'A superior category room with a plush queen bed, soaking tub, and private balcony perfect for unwinding after a day of exploration.',
 2, 'Queen Bed'),

('Superior King Room',
 4000.00,
 'A step up from Deluxe, featuring a larger balcony, premium bedding, a bathtub, and a partial view of Mayon Volcano.',
 2, 'King Bed'),

('Club Level King',
 4500.00,
 'Club floor access with dedicated lounge privileges, express check-in and check-out, and nightly turndown service.',
 2, 'King Bed'),

('Skyline View King',
 4800.00,
 'A high-floor king room with floor-to-ceiling glass framing Legazpi City\'s dramatic skyline and mountain backdrop.',
 2, 'King Bed'),

('Executive King Room',
 5000.00,
 'Tailored for business travellers with an ergonomic workstation, high-speed internet hub, and executive-class amenities.',
 2, 'King Bed'),

('Executive Twin Suite',
 5000.00,
 'Twin-bedded executive suite perfect for colleague sharing, with a separate sitting area and complimentary breakfast.',
 3, 'Twin Beds'),

('Premium Ocean Front',
 5500.00,
 'Front-facing premium room delivering unobstructed sea-to-mountain views, a deep soaking tub, and premium bath products.',
 2, 'King Bed'),

('Two Bedroom Family Suite',
 6500.00,
 'Designed for families, with two separate sleeping areas, kid-friendly amenities, a shared living room, and pool access.',
 4, 'Twin + Queen Bed'),

('Business Class Suite',
 6000.00,
 'A full-featured business suite with a private meeting nook, laptop-sized safe, dual monitors, and express laundry service.',
 2, 'King Bed'),

('Honeymoon Oasis Suite',
 7500.00,
 'A romantic haven with a canopy king bed, rose-petal turndown service, champagne welcome, and a private jacuzzi for two.',
 2, 'King Bed'),

('Junior Suite',
 7000.00,
 'A generous suite with a separate living area, kitchenette, and panoramic views — the ideal balance of space and value.',
 2, 'King Bed'),

('One Bedroom Master Suite',
 8000.00,
 'An expansive master suite with a dedicated living room, walk-in wardrobe, bathtub, and walk-in rain shower.',
 2, 'King Bed'),

('Royal Canopy Suite',
 9000.00,
 'Featuring an elegant four-poster canopy bed, hand-crafted Filipino décor, a private library nook, and butler service.',
 2, 'King Bed'),

('Grand Ambassador Suite',
 10000.00,
 'The choice of dignitaries — a palatial suite with a formal dining area, two bathrooms, and dedicated security ante-room.',
 3, 'King Bed'),

('Panoramic Vista Suite',
 11000.00,
 'A tri-aspect suite with 270° views of Mayon Volcano, Albay Gulf, and the city, plus a wraparound balcony and spa bath.',
 2, 'King Bed'),

('Presidential Suite',
 12000.00,
 'Designed for heads of state and VIP guests: two bedrooms, a grand reception room, private chef\'s kitchen, and butler concierge.',
 4, 'King Bed'),

('Penthouse Luxury Suite',
 15000.00,
 'The pinnacle of luxury. Private rooftop terrace with direct view of Mayon Volcano, butler service, and a private plunge pool.',
 4, 'King Bed');




INSERT INTO amenities (amenity_name, description) VALUES
('Free Wi-Fi',               'High-speed wireless internet throughout the room.'),
('Air Conditioning',         'Individual climate control with remote thermostat.'),
('Flat-screen TV',           '55-inch 4K Smart TV with cable and streaming apps.'),
('Mini Bar',                 'Stocked minibar with local beverages and snacks.'),
('In-Room Safe',             'Electronic in-room safe, large enough for a laptop.'),
('Bathtub',                  'Deep soaking bathtub in the en-suite bathroom.'),
('Walk-in Shower',           'Rainfall shower with premium toiletries.'),
('Private Balcony',          'Private balcony with outdoor seating.'),
('Ocean / Mountain View',    'Unobstructed view of Mayon Volcano or the bay.'),
('Kitchenette',              'Compact kitchen with microwave, fridge, and coffee maker.'),
('Living Area',              'Separate living room with sofa and coffee table.'),
('Butler Service',           '24/7 personal butler on call.'),
('Private Pool',             'Exclusive plunge pool on the private terrace.'),
('Complimentary Breakfast',  'Daily Filipino and international breakfast for two.'),
('Workspace',                'Dedicated desk with ergonomic chair and power outlets.'),
('Jacuzzi',                  'Private in-room or balcony jacuzzi for two.'),
('Club Lounge Access',       'Access to dedicated club lounge with F&B privileges.');


-- ROOMS
-- room_status_id: 1=Available, 2=Occupied, 3=Maintenance, 4=Reserved
-- Floor 1 – Budget | Floor 2 – Mid-Range
-- Floor 3 – Superior | Floor 4 – Suites

INSERT INTO rooms (room_type_id, room_status_id, room_number, floor_number, capacity, price_per_night, description) VALUES

(1,  1, '101', 1, 2,  1800.00, 'Quiet courtyard-view room near the lobby.'),
(2,  1, '102', 1, 2,  1800.00, 'Garden-facing standard double room.'),
(3,  3, '103', 1, 2,  1900.00, 'Currently undergoing repainting.'),
(4,  1, '104', 1, 2,  2200.00, 'Ground-floor terrace with direct garden access.'),
(5,  1, '105', 1, 2,  2500.00, 'Corner studio with kitchenette alcove.'),

(6,  1, '201', 2, 2,  2800.00, 'City-view deluxe room on the second floor.'),
(7,  2, '202', 2, 2,  2900.00, 'Deluxe twin room, currently occupied.'),
(8,  1, '203', 2, 2,  3200.00, 'Premium queen with rainfall shower and workspace.'),
(9,  4, '204', 2, 2,  3500.00, 'Corner room with dual-aspect views, currently reserved.'),
(10, 1, '205', 2, 2,  3800.00, 'Superior queen with soaking tub and balcony.'),

(11, 1, '301', 3, 2,  4000.00, 'Superior king with partial Mayon Volcano view.'),
(12, 1, '302', 3, 2,  4500.00, 'Club level king with lounge access.'),
(13, 2, '303', 3, 2,  4800.00, 'High-floor skyline-view king, currently occupied.'),
(14, 1, '304', 3, 2,  5000.00, 'Executive king configured for business travel.'),
(15, 1, '305', 3, 3,  5000.00, 'Executive twin suite with sitting area.'),
(16, 1, '306', 3, 2,  5500.00, 'Premium ocean-front with bathtub and Mayon views.'),

(17, 1, '401', 4, 4,  6500.00, 'Family suite with connecting rooms and pool access.'),
(18, 1, '402', 4, 2,  6000.00, 'Business class suite with private meeting nook.'),
(19, 1, '403', 4, 2,  7500.00, 'Honeymoon oasis with canopy bed and jacuzzi.'),
(20, 2, '404', 4, 2,  7000.00, 'Junior suite with panoramic view, currently occupied.'),
(21, 1, '405', 4, 2,  8000.00, 'One-bedroom master suite with walk-in wardrobe.'),
(22, 1, '406', 4, 2,  9000.00, 'Royal canopy suite with Filipino heritage décor.'),
(23, 1, '407', 4, 3, 10000.00, 'Grand ambassador suite with formal dining area.'),
(24, 1, '408', 4, 2, 11000.00, 'Panoramic vista suite with 270° mountain and sea view.'),
(25, 4, '409', 4, 4, 12000.00, 'Presidential suite, currently reserved for VIP.'),
(26, 1, '410', 4, 4, 15000.00, 'Penthouse suite – rooftop terrace and plunge pool.');


-- ROOM AMENITIES  (amenity bundles per room type tier)

INSERT INTO room_amenities (room_id, amenity_id) VALUES
(1,1),(1,2),(1,3),(1,5),(1,15),
(2,1),(2,2),(2,3),(2,5),(2,15),
(3,1),(3,2),(3,3),(3,5),(3,15),
(4,1),(4,2),(4,3),(4,5),(4,8),(4,15),
(5,1),(5,2),(5,3),(5,5),(5,10),(5,15),
(6,1),(6,2),(6,3),(6,4),(6,5),(6,7),(6,14),(6,15),
(7,1),(7,2),(7,3),(7,4),(7,5),(7,7),(7,14),(7,15),
(8,1),(8,2),(8,3),(8,4),(8,5),(8,7),(8,14),(8,15),
(9,1),(9,2),(9,3),(9,4),(9,5),(9,7),(9,9),(9,14),(9,15),
(10,1),(10,2),(10,3),(10,4),(10,5),(10,6),(10,7),(10,8),(10,14),(10,15),
(11,1),(11,2),(11,3),(11,4),(11,5),(11,6),(11,7),(11,8),(11,9),(11,14),(11,15),
(12,1),(12,2),(12,3),(12,4),(12,5),(12,6),(12,7),(12,8),(12,9),(12,14),(12,15),(12,17),
(13,1),(13,2),(13,3),(13,4),(13,5),(13,6),(13,7),(13,9),(13,14),(13,15),
(14,1),(14,2),(14,3),(14,4),(14,5),(14,7),(14,9),(14,14),(14,15),
(15,1),(15,2),(15,3),(15,4),(15,5),(15,7),(15,11),(15,14),(15,15),
(16,1),(16,2),(16,3),(16,4),(16,5),(16,6),(16,7),(16,8),(16,9),(16,14),(16,15),
(17,1),(17,2),(17,3),(17,4),(17,5),(17,6),(17,7),(17,8),(17,11),(17,14),(17,15),
(18,1),(18,2),(18,3),(18,4),(18,5),(18,7),(18,14),(18,15),
(19,1),(19,2),(19,3),(19,4),(19,5),(19,6),(19,7),(19,8),(19,9),(19,14),(19,15),(19,16),
(20,1),(20,2),(20,3),(20,4),(20,5),(20,6),(20,7),(20,8),(20,9),(20,10),(20,11),(20,14),(20,15),
(21,1),(21,2),(21,3),(21,4),(21,5),(21,6),(21,7),(21,8),(21,9),(21,10),(21,11),(21,14),(21,15),
(22,1),(22,2),(22,3),(22,4),(22,5),(22,6),(22,7),(22,8),(22,9),(22,11),(22,12),(22,14),(22,15),
(23,1),(23,2),(23,3),(23,4),(23,5),(23,6),(23,7),(23,8),(23,9),(23,11),(23,12),(23,14),(23,15),
(24,1),(24,2),(24,3),(24,4),(24,5),(24,6),(24,7),(24,8),(24,9),(24,10),(24,11),(24,12),(24,14),(24,15),
(25,1),(25,2),(25,3),(25,4),(25,5),(25,6),(25,7),(25,8),(25,9),(25,10),(25,11),(25,12),(25,14),(25,15),(25,16),
(26,1),(26,2),(26,3),(26,4),(26,5),(26,6),(26,7),(26,8),(26,9),(26,10),(26,11),(26,12),(26,13),(26,14),(26,15),(26,16);




INSERT INTO room_images (room_type_id, image_url, caption, display_order) VALUES
(1, 'assets/img/ROOM_IMG/Classic_Courtyard_Room/CC_1.jpg', 'Classic Courtyard Room – main view',   1),
(1, 'assets/img/ROOM_IMG/Classic_Courtyard_Room/CC_2.jpg', 'Classic Courtyard Room – bathroom',    2),
(1, 'assets/img/ROOM_IMG/Classic_Courtyard_Room/CC_3.jpg', 'Classic Courtyard Room – courtyard',   3),
(2, 'assets/img/ROOM_IMG/Standard_Double_Room/ST_1.jpg',   'Standard Double Room – main view',     1),
(2, 'assets/img/ROOM_IMG/Standard_Double_Room/ST_2.jpg',   'Standard Double Room – bathroom',      2),
(2, 'assets/img/ROOM_IMG/Standard_Double_Room/ST_3.jpg',   'Standard Double Room – garden view',   3),
(3, 'assets/img/ROOM_IMG/Standard_Twin_Room/STR_1.jpg',    'Standard Twin Room – twin beds',       1),
(3, 'assets/img/ROOM_IMG/Standard_Twin_Room/STR_2.jpg',    'Standard Twin Room – bathroom',        2),
(3, 'assets/img/ROOM_IMG/Standard_Twin_Room/STR_3.jpg',    'Standard Twin Room – room view',       3),
(4, 'assets/img/ROOM_IMG/Garden_Terrace_Room/GT_1.jpg',    'Garden Terrace – room interior',       1),
(4, 'assets/img/ROOM_IMG/Garden_Terrace_Room/GT_2.jpg',    'Garden Terrace – private terrace',     2),
(4, 'assets/img/ROOM_IMG/Garden_Terrace_Room/GT_3.jpg',    'Garden Terrace – garden view',         3),
(5, 'assets/img/ROOM_IMG/Comfort_Studio_Suite/CSS_1.jpg',  'Comfort Studio – living area',         1),
(5, 'assets/img/ROOM_IMG/Comfort_Studio_Suite/CSS_2.jpg',  'Comfort Studio – kitchenette',         2),
(5, 'assets/img/ROOM_IMG/Comfort_Studio_Suite/CSS_3.jpg',  'Comfort Studio – bedroom',             3),
(6, 'assets/img/ROOM_IMG/Deluxe_Double_Room/DD_1.jpg',     'Deluxe Double – king bed',             1),
(6, 'assets/img/ROOM_IMG/Deluxe_Double_Room/DD_2.jpg',     'Deluxe Double – seating area',         2),
(6, 'assets/img/ROOM_IMG/Deluxe_Double_Room/DD_3.jpg',     'Deluxe Double – city view',            3),
(7, 'assets/img/ROOM_IMG/Deluxe_Twin_Room/DT_1.jpg',       'Deluxe Twin – twin beds',              1),
(7, 'assets/img/ROOM_IMG/Deluxe_Twin_Room/DT_2.jpg',       'Deluxe Twin – bathroom',               2),
(7, 'assets/img/ROOM_IMG/Deluxe_Twin_Room/DT_3.jpg',       'Deluxe Twin – room view',              3),
(8, 'assets/img/ROOM_IMG/Premium_Queen_Room/PQ_1.jpg',     'Premium Queen – bedroom',              1),
(8, 'assets/img/ROOM_IMG/Premium_Queen_Room/PQ_2.jpg',     'Premium Queen – shower',               2),
(8, 'assets/img/ROOM_IMG/Premium_Queen_Room/PQ_3.jpg',     'Premium Queen – workspace',            3),
(9, 'assets/img/ROOM_IMG/Signature_Corner_Room/SC_1.jpg',  'Signature Corner – panoramic view',    1),
(9, 'assets/img/ROOM_IMG/Signature_Corner_Room/SC_2.jpg',  'Signature Corner – bedroom',           2),
(9, 'assets/img/ROOM_IMG/Signature_Corner_Room/SC_3.jpg',  'Signature Corner – dual windows',      3),
(10,'assets/img/ROOM_IMG/Superior_Queen_Room/SQ_1.jpg',    'Superior Queen – bedroom',             1),
(10,'assets/img/ROOM_IMG/Superior_Queen_Room/SQ_2.jpg',    'Superior Queen – balcony',             2),
(10,'assets/img/ROOM_IMG/Superior_Queen_Room/SQ_3.jpg',    'Superior Queen – bathroom',            3),
(11,'assets/img/ROOM_IMG/Superior_King_Room/SK_1.jpg',     'Superior King – bedroom',              1),
(11,'assets/img/ROOM_IMG/Superior_King_Room/SK_2.jpg',     'Superior King – balcony',              2),
(11,'assets/img/ROOM_IMG/Superior_King_Room/SK_3.jpg',     'Superior King – bathroom',             3),
(12,'assets/img/ROOM_IMG/Club_Level_King/CLK_1.jpg',       'Club Level King – bedroom',            1),
(12,'assets/img/ROOM_IMG/Club_Level_King/CLK_2.jpg',       'Club Level King – lounge view',        2),
(12,'assets/img/ROOM_IMG/Club_Level_King/CLK_3.jpg',       'Club Level King – bathroom',           3),
(13,'assets/img/ROOM_IMG/Skyline_View_King/SK_1.jpg',      'Skyline View – bedroom',               1),
(13,'assets/img/ROOM_IMG/Skyline_View_King/SK_2.jpg',      'Skyline View – city panorama',         2),
(13,'assets/img/ROOM_IMG/Skyline_View_King/SK_3.jpg',      'Skyline View – bathroom',              3),
(14,'assets/img/ROOM_IMG/Executive_King_Room/EX_1.jpg',    'Executive King – bedroom',             1),
(14,'assets/img/ROOM_IMG/Executive_King_Room/EX_2.jpg',    'Executive King – workspace',           2),
(14,'assets/img/ROOM_IMG/Executive_King_Room/EX_3.jpg',    'Executive King – bathroom',            3),
(15,'assets/img/ROOM_IMG/Executive_Twin_Suite/EXT_1.jpg',  'Executive Twin – twin beds',           1),
(15,'assets/img/ROOM_IMG/Executive_Twin_Suite/EXT_2.jpg',  'Executive Twin – sitting area',        2),
(15,'assets/img/ROOM_IMG/Executive_Twin_Suite/EXT_3.jpg',  'Executive Twin – bathroom',            3),
(16,'assets/img/ROOM_IMG/Premium_Ocean_Front/P0_1.jpg',    'Premium Ocean Front – bedroom',        1),
(16,'assets/img/ROOM_IMG/Premium_Ocean_Front/P0_2.jpg',    'Premium Ocean Front – sea view',       2),
(16,'assets/img/ROOM_IMG/Premium_Ocean_Front/P0_3.jpg',    'Premium Ocean Front – bathtub',        3),
(17,'assets/img/ROOM_IMG/Two_Bedroom_Family_Suite/TB_1.jpg','Family Suite – twin bedroom',         1),
(17,'assets/img/ROOM_IMG/Two_Bedroom_Family_Suite/TB_2.jpg','Family Suite – living area',          2),
(17,'assets/img/ROOM_IMG/Two_Bedroom_Family_Suite/TB_3.jpg','Family Suite – queen bedroom',        3),
(18,'assets/img/ROOM_IMG/Business_Class_Suite/BC_Suite_1.jpg','Business Suite – bedroom',          1),
(18,'assets/img/ROOM_IMG/Business_Class_Suite/BC_Suite_2.jpg','Business Suite – workspace',        2),
(18,'assets/img/ROOM_IMG/Business_Class_Suite/BC_Suite_3.jpg','Business Suite – bathroom',         3),
(19,'assets/img/ROOM_IMG/Honeymoon_Oasis_Suite/HO_1.jpg',  'Honeymoon Oasis – canopy bed',        1),
(19,'assets/img/ROOM_IMG/Honeymoon_Oasis_Suite/HO_2.jpg',  'Honeymoon Oasis – jacuzzi',           2),
(19,'assets/img/ROOM_IMG/Honeymoon_Oasis_Suite/HO_3.jpg',  'Honeymoon Oasis – balcony',           3),
(20,'assets/img/ROOM_IMG/Junior_Suite/JR_1.jpg',            'Junior Suite – king bed',             1),
(20,'assets/img/ROOM_IMG/Junior_Suite/JR_2.jpg',            'Junior Suite – living area',          2),
(20,'assets/img/ROOM_IMG/Junior_Suite/JR_3.jpg',            'Junior Suite – panoramic view',       3),
(21,'assets/img/ROOM_IMG/One_Bedroom_Master_Suite/MS_1.jpg','Master Suite – bedroom',              1),
(21,'assets/img/ROOM_IMG/One_Bedroom_Master_Suite/MS_2.jpg','Master Suite – living room',          2),
(21,'assets/img/ROOM_IMG/One_Bedroom_Master_Suite/MS_3.jpg','Master Suite – bathroom',             3),
(22,'assets/img/ROOM_IMG/Royal_Canopy_Suite/RC_1.jpg',      'Royal Canopy – canopy bed',           1),
(22,'assets/img/ROOM_IMG/Royal_Canopy_Suite/RC_2.jpg',      'Royal Canopy – lounge',               2),
(22,'assets/img/ROOM_IMG/Royal_Canopy_Suite/RC_3.jpg',      'Royal Canopy – bathroom',             3),
(23,'assets/img/ROOM_IMG/Grand_Ambassador_Suite/GA_1.jpg',  'Ambassador Suite – living area',      1),
(23,'assets/img/ROOM_IMG/Grand_Ambassador_Suite/GA_2.jpg',  'Ambassador Suite – dining area',      2),
(23,'assets/img/ROOM_IMG/Grand_Ambassador_Suite/GA_3.jpg',  'Ambassador Suite – master bedroom',   3),
(24,'assets/img/ROOM_IMG/Panoramic_Vista_Suite/PV_1.jpg',   'Panoramic Vista – main view',         1),
(24,'assets/img/ROOM_IMG/Panoramic_Vista_Suite/PV_2.jpg',   'Panoramic Vista – wraparound terrace',2),
(24,'assets/img/ROOM_IMG/Panoramic_Vista_Suite/PV_3.jpg',   'Panoramic Vista – spa bath',          3),
(25,'assets/img/ROOM_IMG/Presidential_Suite/PS_1.jpg',      'Presidential Suite – reception room', 1),
(25,'assets/img/ROOM_IMG/Presidential_Suite/PS_2.jpg',      'Presidential Suite – master bedroom', 2),
(25,'assets/img/ROOM_IMG/Presidential_Suite/PS_3.jpg',      'Presidential Suite – kitchen',        3),
(26,'assets/img/ROOM_IMG/Penthouse_Luxury_Suite/PH_1.jpg',  'Penthouse – master bedroom',          1),
(26,'assets/img/ROOM_IMG/Penthouse_Luxury_Suite/PH_2.jpg',  'Penthouse – private terrace',         2),
(26,'assets/img/ROOM_IMG/Penthouse_Luxury_Suite/PH_3.jpg',  'Penthouse – plunge pool',             3);


-- STAFF ROLES

INSERT INTO staff_roles (role_name, description) VALUES
('Front Desk Receptionist', 'Handles check-ins, check-outs, and guest inquiries.'),
('Housekeeping Supervisor',  'Oversees room cleaning and linen management.'),
('Food & Beverage Staff',    'Restaurant and room-service operations.'),
('Maintenance Technician',   'Handles room and facility repairs.'),
('Concierge',                'Provides local tour and activity recommendations.');


-- STAFF RECORDS


INSERT INTO staff (staff_role_id, first_name, last_name, email, phone_number, hire_date, staff_status) VALUES
(1, 'Maria',  'Santos',   'maria.santos@pepperlandhotel.com',   '+63-912-001-0001', '2022-03-15', 'Active'),
(1, 'Pedro',  'Reyes',    'pedro.reyes@pepperlandhotel.com',    '+63-912-001-0002', '2022-06-01', 'Active'),
(2, 'Liza',   'Cruz',     'liza.cruz@pepperlandhotel.com',      '+63-912-001-0003', '2021-11-10', 'Active'),
(3, 'Jose',   'Bautista', 'jose.bautista@pepperlandhotel.com',  '+63-912-001-0004', '2023-01-20', 'Active'),
(4, 'Ramon',  'Navarro',  'ramon.navarro@pepperlandhotel.com',  '+63-912-001-0005', '2020-08-05', 'Active'),
(5, 'Carina', 'Flores',   'carina.flores@pepperlandhotel.com',  '+63-912-001-0006', '2023-07-14', 'Active');



-- USER ACCOUNTS
--  "password".



INSERT INTO users
    (role_id, first_name, middle_name, last_name,
     DOB, street_adr, city, region,
     email, phone_number, password_hash, user_status)
VALUES

(1,
 'Hotel', NULL, 'Admin',           
 '1990-01-01',
 '123 Pepperland Avenue', 'Legazpi City', 'Bicol',
 'admin@pepperlandhotel.com',
 '+63-917-000-0001',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Active'),

(3,
 'Front', NULL, 'Staff',           
 '1995-05-15',
 '456 Staff Quarters, Pepperland Avenue', 'Legazpi City', 'Bicol',
 'staff@pepperlandhotel.com',
 '+63-917-000-0002',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Active'),

(2,
 'Juan', 'dela', 'Cruz',
 '1992-08-20',
 '789 Magsaysay Avenue', 'Legazpi City', 'Bicol',
 'juan@example.com',
 '+63-912-345-6789',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Active'),

(2,
 'Ana', 'Maria', 'Reyes',
 '1988-03-12',
 '321 Rizal Street', 'Naga City', 'Bicol',
 'ana.reyes@example.com',
 '+63-918-765-4321',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Active'),

(2,
 'Carlo', 'Jose', 'Mendoza',
 '2000-11-30',
 '55 Penaranda Street', 'Legazpi City', 'Bicol',
 'carlo.mendoza@example.com',
 '+63-915-111-2222',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Active');



-- PROMOTIONS


INSERT INTO promotions (promo_code, promo_name, discount_type, discount_value, start_date, end_date, is_active) VALUES
('WELCOME10',  'Welcome Discount',        'Percentage', 10.00,   '2025-01-01', '2026-12-31', 1),
('SUMMER20',   'Summer Special',          'Percentage', 20.00,   '2025-06-01', '2025-08-31', 0),
('FLAT500',    'Flat ₱500 Off',           'Fixed',      500.00,  '2025-01-01', '2026-12-31', 1),
('MAYON2025',  'Mayon Anniversary Deal',  'Percentage', 15.00,   '2025-09-01', '2025-09-30', 0),
('STAYCATION', 'Staycation Package',      'Fixed',      1000.00, '2026-01-01', '2026-12-31', 1);



-- SAMPLE BOOKINGS + BOOKING_ROOMS + PAYMENTS + REVIEWS


-- Booking 1 (Checked Out)
INSERT INTO bookings
    (user_id, booking_status_id, check_in_date, check_out_date,
     adults_count, children_count, total_amount, booking_date, special_requests)
VALUES (3, 4, '2026-04-10', '2026-04-13', 2, 0, 5400.00, '2026-04-01 10:30:00', 'Ground floor preferred.');

INSERT INTO booking_rooms
    (booking_id, room_id, check_in_date, check_out_date, price_per_night, nights, guest_count)
VALUES (1, 1, '2026-04-10', '2026-04-13', 1800.00, 3, 2);

INSERT INTO payments
    (booking_id, amount_paid, payment_method_id, payment_status_id, transaction_reference)
VALUES (1, 5400.00, 3, 2, 'GCASH-20260401-0001');

INSERT INTO reviews (booking_id, user_id, rating, title, comment, review_status)
VALUES (1, 3, 5, 'Excellent Stay!',
        'The room was spotless and the staff were very friendly. Will definitely come back.',
        'Approved');

-- Booking 2 (Confirmed)
INSERT INTO bookings
    (user_id, booking_status_id, check_in_date, check_out_date,
     adults_count, children_count, total_amount, booking_date, special_requests)
VALUES (4, 2, '2026-06-15', '2026-06-18', 2, 0, 8400.00, '2026-05-20 14:15:00', 'High floor if possible.');

INSERT INTO booking_rooms
    (booking_id, room_id, check_in_date, check_out_date, price_per_night, nights, guest_count)
VALUES (2, 6, '2026-06-15', '2026-06-18', 2800.00, 3, 2);

INSERT INTO payments
    (booking_id, amount_paid, payment_method_id, payment_status_id, transaction_reference)
VALUES (2, 8400.00, 1, 2, 'CARD-20260520-0002');

-- Booking 3 (Pending)
INSERT INTO bookings
    (user_id, booking_status_id, check_in_date, check_out_date,
     adults_count, children_count, total_amount, booking_date, special_requests)
VALUES (5, 1, '2026-07-01', '2026-07-05', 2, 2, 26000.00, '2026-05-24 09:00:00', 'Extra child cot please.');

INSERT INTO booking_rooms
    (booking_id, room_id, check_in_date, check_out_date, price_per_night, nights, guest_count)
VALUES (3, 17, '2026-07-01', '2026-07-05', 6500.00, 4, 4);

-- Booking 4 (Checked In – dynamic dates)
INSERT INTO bookings
    (user_id, booking_status_id, check_in_date, check_out_date,
     adults_count, children_count, total_amount, booking_date, special_requests)
VALUES (3, 3, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 2, 0, 21000.00,
        DATE_SUB(NOW(), INTERVAL 7 DAY), 'Early check-in requested.');

INSERT INTO booking_rooms
    (booking_id, room_id, check_in_date, check_out_date, price_per_night, nights, guest_count)
VALUES (4, 20, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 7000.00, 3, 2);

INSERT INTO payments
    (booking_id, amount_paid, payment_method_id, payment_status_id, transaction_reference)
VALUES (4, 21000.00, 5, 2, 'CASH-20260517-0004');

-- Booking 5 (Cancelled)
INSERT INTO bookings
    (user_id, booking_status_id, check_in_date, check_out_date,
     adults_count, children_count, total_amount, booking_date, special_requests)
VALUES (4, 5, '2026-05-01', '2026-05-03', 2, 0, 8000.00, '2026-04-25 11:00:00', 'No special requests.');

INSERT INTO booking_rooms
    (booking_id, room_id, check_in_date, check_out_date, price_per_night, nights, guest_count)
VALUES (5, 11, '2026-05-01', '2026-05-03', 4000.00, 2, 2);



-- CONTACT MESSAGES


INSERT INTO contact_messages (name, email, subject, message, message_status) VALUES
('Maria Santos',  'maria.s@example.com',   'Room availability inquiry',
 'Hi, I would like to ask if the Penthouse Suite is available for the last week of July 2026. We are a group of 4 adults. Thank you.',
 'New'),

('Roberto Cruz',  'rcruz@gmail.com',        'Special anniversary package',
 'Good day! My wife and I are celebrating our 10th anniversary on June 28. Do you offer any special packages or room decorations? We are interested in the Honeymoon Oasis Suite.',
 'New'),

('Tourism Office', 'tourism@legazpi.gov',   'Partnership inquiry',
 'We would like to discuss a possible referral partnership for incoming tourists to Legazpi City. Kindly contact us at your earliest convenience.',
 'Read');