# ACTORS

    - admin
    - farmers

# ACTIONS

    - login [
        email
        password
    ]

# STATISTICS | DASHBOARD

    - total_revenue
    - total_farmers
    - orders [
        total
    ]
    - warehouse [
        total
        active
        inactive
    ]

# ACTIVITIES

    [
        action
        description
        time
    ]

---

## Extensions

    [
        technical support
        consultations
        training & education
        farmer support
    ]

## Services

    [
        installation
        warehouse
        agro equipment
    ]

## Communities

    [
        farmers
        exporters
        traders
    ]

---

## [crop_types]

-   name []

## technical_supports

-   full_name
-   phone_number
-   email
-   crop_type_id
-   stage_of_plant
-   problem_with_crop

## payment_accounts

-   account_name
-   account_number
-   bank_name

## contact_lines

-   whatsapp_name [Mr. Smith]
-   whatsapp_number [2349091922467](13)
-   status [active | inactive]

## consultations

-   full_name
-   phone_number
-   email
-   consultation_time
-   description

---

## [product_types]

-   name [
    agro product
    warehouse
    ]

## [product_categories]

-   name [
    seeds & plant
    agro inputs
    agro outputs
    equipment & mechanization
    ]

## products

-   banner
-   name
-   description
-   price
-   location
-   tag [new, trending]
-   status [active | inactive]

-   sku
-   stock
-   estimated_delivery [3, 5, 7 days]
-   product_type_id []
-   category_id []

-   moq, or Minimum Order Quantity, is the smallest number of units a supplier will sell in a single order.
-   specs generally refers to the product specifications or technical details of an item being sold online

## product_images[]

## product_reviews

-   product_id
-   ratings [1, 3, 5]
-   review
-   status [approve, pending, rejected]

---------This information should be sent to whatsapp after being saved to DB-----------

## orders

-   full_name
-   phone_number
-   email
-   address
-   total_price
-   status [pending, confirmed, processing, shipped, delivered, cancelled]
-   updated_by
-   user_id (if buyer can have account)

## order_items

-   order_id
-   product_id
-   quantity
-   unit_price
-   total_price

## [farming_interests]

-   name []

## training_programs

-   full_name
-   phone_number
-   email
-   farming_interest_id

## warehouses

-   banner
-   name
-   sku
-   description
-   price
-   location
-   tag [new, trending]
-   status [active | inactive]
-   capacity [string]

## warehouse_images[]

## warehouse_reviews

-   warehouse_id
-   ratings [1, 3, 5]
-   comment

## warehouse_orders

-   warehouse_id
-   full_name
-   phone_number
-   email
-   address
-   total_price
-   status [pending, confirmed, processing, shipped, delivered, cancelled]
-   updated_by
-   user_id (if buyer can have account)

---

## Transactions

-   user_id
-   warehouse_order_id (warehouse)
-   order_id (order)
-   payment_type [warehouse | order]
-   full_name
-   email
-   amount
-   status
-   reference
-   payment_method
-   payment_provider
-   data

## [type_of_farmings]

-   name [
    crop farming
    livestock
    mixed farming
    others
    ]

## farmers

-   full_name
-   phone_number
-   email
-   country
-   state
-   address
-   farm_name
-   farm_size
-   type_of_farming_id
-   main_products
-   do_you_own_farming_equipment [yes|no]
-   where_do_you_sell_your_products
-   challenge_in_selling_your_products
-   additional_comment

---

## [assistance_types]

-   name []

## farm_assistance

-   full_name
-   phone_number
-   email
-   assistance_types_id
-   reason_for_request

## [installation_types]

-   name [
    greenhouse structure
    hydroponic system setup
    drip irrigation installation
    ]

## installation_services

-   full_name
-   phone_number
-   email
-   farm_size
-   installation_type_id
-   form_location
-   notes
-   status

## [equipment_types]

-   name [
    tractor
    shovel
    ]

## rental_services

-   full_name
-   phone_number
-   email
-   farm_size
-   farm_size_unit
-   equipment_type_id
-   address
-   state
-   renting_purpose
-   duration [7, 14 days]
-   duration_unit ['days', 'weeks', 'months', 'years']
-   amount
-   notes
-   status
-   created_by
-   updated_by

## **_WORKING ON THIS_**

---

## fags

-   question
-   answer

## newsletters

-   email

## blogs

    author_id
    banner
    title
    content
    views

## guides

    banner
    title
    content

## partners

    name
    asset_id
        assets.url

## quotes

    job_title [Dr.]
    name [Muh Bello]
    organization [CBN]
    content
    asset_id
        assets.url

## testimonials

    job_title [Dr.]
    name [Muh Bello]
    organization [CBN]
    content
    asset_id
        assets.url

## socials

-   name
-   url
-   status

    facebook_url
    x_url
    linkedin_url
    instagram_url
    youtube_url
    whatsapp_url
