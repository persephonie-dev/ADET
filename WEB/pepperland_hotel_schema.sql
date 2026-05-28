
CREATE DATABASE IF NOT EXISTS pepperland_hotel
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE pepperland_hotel;


CREATE TABLE hotel_profile
(
    profile_id       INT           NOT NULL AUTO_INCREMENT,
    hotel_name       VARCHAR(150)  NOT NULL DEFAULT 'The Pepperland Hotel',
    description      TEXT,
    star_rating      DECIMAL(2,1),
    contact_email    VARCHAR(150),
    contact_phone    VARCHAR(20),
    website_url      VARCHAR(255),
    street_address   VARCHAR(255)  NOT NULL,
    city             VARCHAR(100)  NOT NULL,
    province         VARCHAR(100)  NOT NULL,
    country          VARCHAR(100)  NOT NULL,
    postal_code      VARCHAR(20),
    check_in_time    TIME,
    check_out_time   TIME,

    PRIMARY KEY (profile_id)
);



CREATE TABLE room_types
(
    room_type_id   INT           NOT NULL AUTO_INCREMENT,
    type_name      VARCHAR(100)  NOT NULL,
    base_price     DECIMAL(10,2) NOT NULL CHECK (base_price >= 0),
    description    TEXT,
    max_capacity   INT,
    bed_type       VARCHAR(100),

    PRIMARY KEY (room_type_id),
    UNIQUE KEY uq_room_type_name (type_name)
);




CREATE TABLE room_images
(
    room_image_id  INT           NOT NULL AUTO_INCREMENT,
    room_type_id   INT           NOT NULL,
    image_url      VARCHAR(255)  NOT NULL,
    caption        VARCHAR(255),
    display_order  INT,

    PRIMARY KEY (room_image_id),

    CONSTRAINT fk_ri_room_type
        FOREIGN KEY (room_type_id)
        REFERENCES room_types(room_type_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);



CREATE TABLE room_status
(
    room_status_id  INT          NOT NULL AUTO_INCREMENT,
    status_name     VARCHAR(50)  NOT NULL,

    PRIMARY KEY (room_status_id),
    UNIQUE KEY uq_room_status_name (status_name)
);



CREATE TABLE amenities
(
    amenity_id    INT           NOT NULL AUTO_INCREMENT,
    amenity_name  VARCHAR(100)  NOT NULL,
    description   TEXT,

    PRIMARY KEY (amenity_id),
    UNIQUE KEY uq_amenity_name (amenity_name)   -- was missing in hotel_schema
);



CREATE TABLE rooms
(
    room_id           INT            NOT NULL AUTO_INCREMENT,
    room_type_id      INT            NOT NULL,
    room_status_id    INT            NOT NULL,
    room_number       VARCHAR(20)    NOT NULL,
    floor_number      INT,
    capacity          INT,
    price_per_night   DECIMAL(10,2)  NOT NULL CHECK (price_per_night >= 0),
    description       TEXT,
    created_at        DATETIME       DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME       DEFAULT CURRENT_TIMESTAMP
                                     ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (room_id),
    UNIQUE KEY uq_room_number (room_number),

    CONSTRAINT fk_rooms_type
        FOREIGN KEY (room_type_id)
        REFERENCES room_types(room_type_id)
        ON UPDATE CASCADE,

    CONSTRAINT fk_rooms_status
        FOREIGN KEY (room_status_id)
        REFERENCES room_status(room_status_id)
        ON UPDATE CASCADE
);



CREATE TABLE room_amenities
(
    room_id     INT  NOT NULL,
    amenity_id  INT  NOT NULL,

    PRIMARY KEY (room_id, amenity_id),

    CONSTRAINT fk_ra_room
        FOREIGN KEY (room_id)
        REFERENCES rooms(room_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_ra_amenity
        FOREIGN KEY (amenity_id)
        REFERENCES amenities(amenity_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);



CREATE TABLE roles
(
    role_id      INT          NOT NULL AUTO_INCREMENT,
    role_name    VARCHAR(50)  NOT NULL,
    description  TEXT,

    PRIMARY KEY (role_id),
    UNIQUE KEY uq_role_name (role_name)   -- was missing in hotel_schema
);



CREATE TABLE users
(
    user_id        INT           NOT NULL AUTO_INCREMENT,
    role_id        INT           NOT NULL,
    first_name     VARCHAR(100)  NOT NULL,
    middle_name    VARCHAR(100)  NULL,         
    last_name      VARCHAR(100)  NOT NULL,
    DOB            DATE          NOT NULL,
    street_adr     VARCHAR(255)  NOT NULL,
    city           VARCHAR(100)  NOT NULL,
    region         VARCHAR(50)   NOT NULL,
    email          VARCHAR(150)  NOT NULL,
    phone_number   VARCHAR(20),
    password_hash  VARCHAR(255)  NOT NULL,
    user_status    VARCHAR(20)   NOT NULL DEFAULT 'Active',
    created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME      DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id),
    UNIQUE KEY uq_user_email (email),

    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id)
        REFERENCES roles(role_id)
        ON UPDATE CASCADE
);




CREATE TABLE staff_roles
(
    staff_role_id  INT           NOT NULL AUTO_INCREMENT,
    role_name      VARCHAR(100)  NOT NULL,
    description    TEXT,

    PRIMARY KEY (staff_role_id),
    UNIQUE KEY uq_staff_role_name (role_name)
);




CREATE TABLE staff
(
    staff_id       INT           NOT NULL AUTO_INCREMENT,
    staff_role_id  INT           NOT NULL,
    first_name     VARCHAR(100)  NOT NULL,
    last_name      VARCHAR(100)  NOT NULL,
    email          VARCHAR(150)  NOT NULL,
    phone_number   VARCHAR(20)   NOT NULL,
    hire_date      DATE,
    staff_status   VARCHAR(20)   DEFAULT 'Active',

    PRIMARY KEY (staff_id),
    UNIQUE KEY uq_staff_email (email),   

    CONSTRAINT fk_staff_role
        FOREIGN KEY (staff_role_id)
        REFERENCES staff_roles(staff_role_id)
        ON UPDATE CASCADE
);


-- BOOKING STATUS

CREATE TABLE booking_status
(
    booking_status_id  INT          NOT NULL AUTO_INCREMENT,
    status_name        VARCHAR(50)  NOT NULL,

    PRIMARY KEY (booking_status_id),
    UNIQUE KEY uq_booking_status_name (status_name)
);




CREATE TABLE bookings
(
    booking_id          INT            NOT NULL AUTO_INCREMENT,
    user_id             INT,                        
    booking_status_id   INT            NOT NULL,
    check_in_date       DATE           NOT NULL,
    check_out_date      DATE           NOT NULL,
    adults_count        INT            NOT NULL,
    children_count      INT            DEFAULT 0,
    total_amount        DECIMAL(10,2),
    booking_date        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    special_requests    TEXT           NULL,           -- FIX: TEXT NULL (no default needed)
    created_at          DATETIME       DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME       DEFAULT CURRENT_TIMESTAMP
                                       ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (booking_id),

    INDEX idx_bookings_user (user_id),

    CONSTRAINT chk_booking_dates
        CHECK (check_out_date > check_in_date),

    CONSTRAINT fk_bookings_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE SET NULL          -- FIX: was "ON SET NULL" (invalid syntax)
        ON UPDATE CASCADE,

    CONSTRAINT fk_bookings_status
        FOREIGN KEY (booking_status_id)
        REFERENCES booking_status(booking_status_id)
        ON UPDATE CASCADE
);



CREATE TABLE booking_rooms
(
    booking_id       INT            NOT NULL,
    room_id          INT            NOT NULL,
    check_in_date    DATE           NOT NULL,
    check_out_date   DATE           NOT NULL,
    price_per_night  DECIMAL(10,2)  NOT NULL,
    nights           INT            NOT NULL,
    guest_count      INT,

    PRIMARY KEY (booking_id, room_id),

    CONSTRAINT fk_br_booking
        FOREIGN KEY (booking_id)
        REFERENCES bookings(booking_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_br_room
        FOREIGN KEY (room_id)
        REFERENCES rooms(room_id)
        ON UPDATE CASCADE
);



CREATE TABLE payment_methods
(
    payment_method_id  INT          NOT NULL AUTO_INCREMENT,
    method_name        VARCHAR(50)  NOT NULL,

    PRIMARY KEY (payment_method_id),
    UNIQUE KEY uq_payment_method_name (method_name)
);




CREATE TABLE payment_status
(
    payment_status_id  INT          NOT NULL AUTO_INCREMENT,
    status_name        VARCHAR(50)  NOT NULL,

    PRIMARY KEY (payment_status_id),
    UNIQUE KEY uq_payment_status_name (status_name)
);


CREATE TABLE payments
(
    payment_id              INT            NOT NULL AUTO_INCREMENT,
    booking_id              INT            NOT NULL,
    amount_paid             DECIMAL(10,2)  NOT NULL CHECK (amount_paid >= 0),
    payment_method_id       INT            NOT NULL,
    payment_status_id       INT            NOT NULL,
    transaction_reference   VARCHAR(100)   NOT NULL,
    created_at              DATETIME       DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME       DEFAULT CURRENT_TIMESTAMP
                                           ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (payment_id),
    UNIQUE KEY uq_transaction_reference (transaction_reference),

    CONSTRAINT fk_payments_booking
        FOREIGN KEY (booking_id)
        REFERENCES bookings(booking_id)
        ON UPDATE CASCADE,

    CONSTRAINT fk_payments_method
        FOREIGN KEY (payment_method_id)
        REFERENCES payment_methods(payment_method_id)
        ON UPDATE CASCADE,

    CONSTRAINT fk_payments_status
        FOREIGN KEY (payment_status_id)
        REFERENCES payment_status(payment_status_id)
        ON UPDATE CASCADE
);




CREATE TABLE reviews
(
    review_id      INT           NOT NULL AUTO_INCREMENT,
    booking_id     INT           NOT NULL,
    user_id        INT           NOT NULL,
    rating         INT           NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title          VARCHAR(150),
    comment        TEXT,
    review_date    DATETIME      DEFAULT CURRENT_TIMESTAMP,
    review_status  VARCHAR(20)   NOT NULL DEFAULT 'Pending',
    created_at     DATETIME      DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME      DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (review_id),
    UNIQUE KEY uq_review_booking (booking_id),   -- 1 review per booking

    CONSTRAINT fk_reviews_booking
        FOREIGN KEY (booking_id)
        REFERENCES bookings(booking_id)
        ON UPDATE CASCADE,

    CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
);



CREATE TABLE promotions
(
    promotion_id    INT            NOT NULL AUTO_INCREMENT,
    promo_code      VARCHAR(50)    NOT NULL,
    promo_name      VARCHAR(150)   NOT NULL,
    discount_type   VARCHAR(20)    NOT NULL,   -- 'Percentage' | 'Fixed'
    discount_value  DECIMAL(10,2)  NOT NULL CHECK (discount_value >= 0),
    start_date      DATE           NOT NULL,
    end_date        DATE           NOT NULL,
    is_active       BOOLEAN        DEFAULT TRUE,

    PRIMARY KEY (promotion_id),
    UNIQUE KEY uq_promo_code (promo_code)
);




CREATE TABLE booking_promotions
(
    booking_id    INT  NOT NULL,
    promotion_id  INT  NOT NULL,

    PRIMARY KEY (booking_id, promotion_id),

    CONSTRAINT fk_bp_booking
        FOREIGN KEY (booking_id)
        REFERENCES bookings(booking_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_bp_promotion
        FOREIGN KEY (promotion_id)
        REFERENCES promotions(promotion_id)
        ON UPDATE CASCADE
);



CREATE TABLE contact_messages
(
    message_id      INT           NOT NULL AUTO_INCREMENT,
    name            VARCHAR(150),
    email           VARCHAR(150)  NOT NULL,
    subject         VARCHAR(150)  NOT NULL,
    message         TEXT          NOT NULL,
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    message_status  VARCHAR(20)   NOT NULL DEFAULT 'New',   -- 'New'|'Read'|'Replied'

    PRIMARY KEY (message_id)
);