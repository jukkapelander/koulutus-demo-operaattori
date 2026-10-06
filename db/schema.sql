-- Operaattori-demo: skeema (MariaDB). HUOM: koulutuskäyttöön tehty demo, ei tuotantomalli.

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS reference_payments;
DROP TABLE IF EXISTS bank_batches;
DROP TABLE IF EXISTS invoice_rows;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS parties;
DROP TABLE IF EXISTS operators;
SET FOREIGN_KEY_CHECKS = 1;

-- Operaattorirekisteri (välittäjät). operator_id on pankin BIC-pohjainen välittäjätunnus.
CREATE TABLE operators (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120) NOT NULL,
    operator_id   VARCHAR(35)  NOT NULL,
    active        TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Osapuolet (lähettäjät ja vastaanottajat). ovt = OVT-tunnus, operator_id = osapuolen operaattori.
CREATE TABLE parties (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(160) NOT NULL,
    business_id   VARCHAR(12),
    ovt           VARCHAR(20)  NOT NULL,
    iban          VARCHAR(34),
    bic           VARCHAR(11),
    operator_id   VARCHAR(35)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- EDI-sanomat
CREATE TABLE messages (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    message_id    VARCHAR(64)  NOT NULL,
    type          VARCHAR(20)  NOT NULL,
    direction     VARCHAR(10)  NOT NULL,
    sender_ovt    VARCHAR(20),
    recipient_ovt VARCHAR(20),
    from_operator VARCHAR(35),
    to_operator   VARCHAR(35),
    raw_path      VARCHAR(255),
    status        VARCHAR(20)  NOT NULL DEFAULT 'received',
    created_at    DATETIME     NOT NULL,
    routed_at     DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Finvoice-laskun jäsennetyt kentät
CREATE TABLE invoices (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    message_id     INT NOT NULL,
    invoice_number VARCHAR(40),
    invoice_date   DATE,
    due_date       DATE,
    seller_iban    VARCHAR(34),
    reference      VARCHAR(30),
    gross_total    DECIMAL(12,2) NOT NULL DEFAULT 0,
    vat_total      DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_invoice_message FOREIGN KEY (message_id) REFERENCES messages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE invoice_rows (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id   INT NOT NULL,
    description  VARCHAR(255),
    net          DECIMAL(12,2) NOT NULL DEFAULT 0,
    vat_percent  DECIMAL(5,2)  NOT NULL DEFAULT 0,
    CONSTRAINT fk_row_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pankkiaineistoerät
CREATE TABLE bank_batches (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    filename     VARCHAR(255) NOT NULL,
    format       VARCHAR(20)  NOT NULL,
    received_at  DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Saapuvat viitemaksut
CREATE TABLE reference_payments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    reference       VARCHAR(30)  NOT NULL,
    amount          DECIMAL(12,2) NOT NULL,
    paid_date       DATE         NOT NULL,
    archive_id      VARCHAR(40),
    matched_invoice INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
