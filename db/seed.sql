-- Operaattori-demo: testiaineisto. Kaikki yritykset, tunnukset ja tilinumerot ovat keksittyjä.

INSERT INTO operators (name, operator_id, active) VALUES
    ('Maksajan Operaattori Oy',  'MAOPFIH1', 1),
    ('Verkkolasku Suomi Oy',     'VLASFIH2', 1),
    ('Itä-Suomen Välitys Oy',    'ISVAFIH3', 0);

INSERT INTO parties (name, business_id, ovt, iban, bic, operator_id) VALUES
    ('Pohjolan Pakkaus Oy',   '2345678-0', '003723456780', 'FI1011112222333344', 'OKOYFIHH', 'MAOPFIH1'),
    ('Kuusamon Kahvila Oy',   '1234567-1', '003712345671', 'FI4822223333444455', 'NDEAFIHH', 'VLASFIH2'),
    ('Tammerkosken Tili Ky',  '3456781-4', '003734567814', 'FI8440550010101010', 'DABAFIHH', 'MAOPFIH1'),
    ('Nokian Nostot Oy',      '7654321-2', '003776543212', 'FI9350000120202020', 'HELSFIHH', 'VLASFIH2'),
    ('Kymen Kuljetus Oy',     '2224442-8', '003722244428', 'FI5280001330303030', 'OKOYFIHH', 'ISVAFIH3'),
    ('Lahden Leipä Oy',       '5566778-5', '003755667785', 'FI1011112222333345', 'OKOYFIHH', 'MAOPFIH1'),
    ('Oulun Optiikka Oy',     '4455667-6', '0088 12345',   'FI4822223333444455', 'NDEAFIHH', 'VLASFIH2');

INSERT INTO messages (message_id, type, direction, sender_ovt, recipient_ovt, from_operator, to_operator, raw_path, status, created_at, routed_at) VALUES
    ('MSG-1001', 'finvoice', 'inbound', '003723456780', '003712345671', 'MAOPFIH1', 'VLASFIH2', NULL, 'delivered', '2026-09-01 08:00:00', '2026-09-01 08:00:05'),
    ('MSG-1002', 'finvoice', 'inbound', '003734567814', '003776543212', 'MAOPFIH1', 'VLASFIH2', NULL, 'delivered', '2026-09-02 09:10:00', '2026-09-02 09:10:04'),
    ('MSG-1001', 'finvoice', 'inbound', '003723456780', '003712345671', 'MAOPFIH1', 'VLASFIH2', NULL, 'delivered', '2026-09-01 08:03:00', '2026-09-01 08:03:05'),
    ('MSG-1003', 'finvoice', 'inbound', '003712345671', 'MAOPFIH1',    'MAOPFIH1', 'VLASFIH2', NULL, 'routed',    '2026-09-03 10:00:00', '2026-09-03 10:00:03'),
    ('MSG-1004', 'finvoice', 'inbound', '003723456780', '003776543212', 'MAOPFIH1', 'ISVAFIH3', NULL, 'routed',    '2026-09-04 11:00:00', '2026-09-04 11:00:02');

INSERT INTO invoices (message_id, invoice_number, invoice_date, due_date, seller_iban, reference, gross_total, vat_total) VALUES
    (1, 'INV-5001', '2026-08-25', '2026-09-08', 'FI1011112222333344', '12506',  1550.00, 300.00),
    (2, 'INV-5002', '2026-08-26', '2026-09-09', 'FI8440550010101010', '43009',  4300.00, 832.26),
    (4, 'INV-5003', '2026-08-27', '2026-09-26', 'FI4822223333444455', '9807',   1200.00, 235.20),
    (5, 'INV-5004', '2026-08-28', '2026-09-27', 'FI9350000120202020', '154008', 15400.00, 2980.65);

INSERT INTO invoice_rows (invoice_id, description, net, vat_percent) VALUES
    (1, 'Pakkausmateriaali', 1250.00, 24.00),
    (2, 'Kirjanpitopalvelu', 3467.74, 24.00),
    (3, 'Nostopalvelu', 980.00, 24.00),
    (4, 'Kuljetus', 12419.35, 24.00);

INSERT INTO bank_batches (filename, format, received_at) VALUES
    ('viitemaksut_2026-09.csv', 'reference', '2026-10-01 06:00:00');

INSERT INTO reference_payments (reference, amount, paid_date, archive_id, matched_invoice) VALUES
    ('12506', 1550.00, '2026-09-05', 'B-9001', 1);
