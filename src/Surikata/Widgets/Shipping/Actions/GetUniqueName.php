<?php
namespace ADIOS\Actions\Shipping;

class GetUniqueName extends \ADIOS\Core\Widget\Action {
  public function render() {

    if ((int)$this->params['shipmentId'] != 0) {

      $shipment = new \ADIOS\Widgets\Shipping\Models\Shipment($this->adios);

      $shipmentQuery = reset($shipment
        ->with([
          'delivery:id,name',
          'payment:id,name',
          'country:id,name'
        ])
        ->where('id', $this->params['shipmentId'])
        ->get()
        ->toArray()
        );

      return 
        $shipmentQuery['delivery']['name']
        . "|" . $shipmentQuery['payment']['name']
        . "|" . $shipmentQuery['country']['name']
        . "|" . $shipmentQuery['name'] . "|" .
        trim(
          ($this->params['method'] == 1
          ?
            "
              price from {$this->params['price_from']} to {$this->params['price_to']}
            "
          :
            "
              weight from {$this->params['weight_from']} to {$this->params['weight_to']}
            "
          )
        )
      ;

    }
  }
}