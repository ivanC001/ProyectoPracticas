<?php

namespace App\Domains\Comprobantes\Extenciones;

use Greenter\Model\Sale\Invoice;

/**
 * Class CustomInvoice.
 * Extiende la funcionalidad de la clase Invoice para agregar atributos personalizados.
 */
class CustomInvoice  extends Invoice
{
    private $customizationID;

    /**
     * Establecer el Customization ID.
     *
     * @param string $customizationID
     * @return $this
     */
    public function setCustomizationID(string $customizationID): self
    {
        $this->customizationID = $customizationID;
        return $this;
    }

    /**
     * Obtener el Customization ID.
     *
     * @return string|null
     */
    public function getCustomizationID(): ?string
    {
        return $this->customizationID;
    }

}
