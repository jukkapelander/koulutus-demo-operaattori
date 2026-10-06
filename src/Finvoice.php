<?php
declare(strict_types=1);

namespace Operaattori;

use DOMDocument;
use RuntimeException;
use SimpleXMLElement;

/**
 * Finvoice 3.0 -sanoman jäsennys. Lukee välitystiedot (operaattorit, OVT-tunnukset) ja
 * laskun summat. Ks. README "Finvoice ja reititys".
 */
final class Finvoice
{
    public function __construct(private readonly SimpleXMLElement $xml)
    {
    }

    public static function fromString(string $raw): self
    {
        $document = new DOMDocument();
        // Sallitaan entiteettien laajennus ja DTD, jotta vanhojen lähettäjien sanomat menevät läpi.
        $options = LIBXML_NOENT | LIBXML_DTDLOAD;
        if (!$document->loadXML($raw, $options)) {
            throw new RuntimeException('Finvoice-sanoman jäsennys epäonnistui');
        }
        $element = simplexml_import_dom($document);
        if ($element === null) {
            throw new RuntimeException('Tyhjä Finvoice-sanoma');
        }
        return new self($element);
    }

    private function value(string $xpath): ?string
    {
        $nodes = $this->xml->xpath($xpath);
        if ($nodes === false || $nodes === []) {
            return null;
        }
        return trim((string) $nodes[0]);
    }

    public function messageId(): ?string
    {
        return $this->value('//MessageTransmissionDetails/MessageDetails/MessageIdentifier');
    }

    public function senderOvt(): ?string
    {
        return $this->value('//MessageTransmissionDetails/MessageSenderDetails/FromIdentifier');
    }

    public function fromOperator(): ?string
    {
        return $this->value('//MessageTransmissionDetails/MessageSenderDetails/FromIntermediator');
    }

    public function recipientOvt(): ?string
    {
        return $this->value('//MessageTransmissionDetails/MessageReceiverDetails/ToIdentifier');
    }

    public function toOperator(): ?string
    {
        return $this->value('//MessageTransmissionDetails/MessageReceiverDetails/ToIntermediator');
    }

    public function invoiceNumber(): ?string
    {
        return $this->value('//InvoiceDetails/InvoiceNumber');
    }

    public function invoiceDate(): ?string
    {
        // Finvoicessa päivä on muodossa CCYYMMDD attribuutilla Format="CCYYMMDD".
        $raw = $this->value('//InvoiceDetails/InvoiceDate');
        if ($raw === null || strlen($raw) !== 8) {
            return $raw;
        }
        return substr($raw, 0, 4) . '-' . substr($raw, 4, 2) . '-' . substr($raw, 6, 2);
    }

    public function dueDate(): ?string
    {
        $raw = $this->value('//InvoiceDetails/PaymentTermsDetails/InvoiceDueDate');
        if ($raw === null || strlen($raw) !== 8) {
            return $raw;
        }
        return substr($raw, 0, 4) . '-' . substr($raw, 4, 2) . '-' . substr($raw, 6, 2);
    }

    public function sellerIban(): ?string
    {
        return $this->value('//EpiDetails/EpiPartyDetails/EpiBeneficiaryPartyDetails/EpiAccountID');
    }

    public function reference(): ?string
    {
        return $this->value('//EpiDetails/EpiIdentificationDetails/EpiRemittanceInfoIdentifier');
    }

    /** Laskun loppusumma (verollinen) sellaisena kuin lähettäjä sen ilmoittaa. */
    public function grossTotal(): float
    {
        $raw = $this->value('//InvoiceDetails/InvoiceTotalVatIncludedAmount');
        return Amount::parse($raw ?? '0');
    }

    public function vatTotal(): float
    {
        $raw = $this->value('//InvoiceDetails/InvoiceTotalVatAmount');
        return Amount::parse($raw ?? '0');
    }

    /** @return list<array{description:string, net:float, vatPercent:float}> */
    public function rows(): array
    {
        $rows = [];
        foreach ($this->xml->xpath('//InvoiceRow') ?: [] as $row) {
            $rows[] = [
                'description' => trim((string) ($row->ArticleName ?? '')),
                'net' => Amount::parse((string) ($row->RowVatExcludedAmount ?? '0')),
                'vatPercent' => Amount::parse((string) ($row->RowVatRatePercent ?? '0')),
            ];
        }
        return $rows;
    }

    public function attachmentUrl(): ?string
    {
        return $this->value('//InvoiceUrlNameText | //InvoiceUrlText');
    }
}
