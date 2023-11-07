<?php
/**
 * Epack Controller Class
 */

namespace Epack;

class Controller {

	// Controller call.
	public function __call($name, $args) {
		$method = $name . 'Action';

		if ( method_exists($this, $method) ) {
			if ( $this->before() !== false ) {
				call_user_func_array(
					[$this, $method], $args
				);

				$this->after();
			}

		} else {
			throw new \Exception("Method {$method} not found in controller " . get_class($this));
		}
	}

	// Controller before method.
	protected function before() {
		# code...
	}

	// Controller after method.
	protected function after() {
		# code...
	}

}
