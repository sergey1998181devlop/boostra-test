<?php

declare(strict_types=1);

namespace lib\VirtualCard;

class DeleteVirtualCardAction
{
	private \Config $config;

	public function __construct(\Config $config)
	{
		$this->config = $config;
	}

	/**
	 * Delete a virtual card if the order has one
	 *
	 * @param object $order
	 * @param bool $synch
	 * @return array|null
	 */
	public function execute(object $order, bool $synch = false): ?array
	{
		try {
			$vcService = new VirtualCardService(
				$this->config,
				(int)$order->user_id,
				(int)$order->order_id
			);

			return $vcService->deleteCard($synch);
		} catch (\Throwable $e) {
			return null;
		}
	}
}
