<?php
class MiEnumsAppController extends AppController {
	function _back($steps = 1, $force = false) {
		if (isset($this->SwissArmy)) {
			if (($force || in_array($this->action, $this->postActions)) && $this->RequestHandler->isAjax()) {
				$url = $this->SwissArmy->back($steps, null, false);
				return $this->redirect($url, null, true, true);
			}
			return $this->SwissArmy->back($steps);
		}
		return $this->redirect($this->referer('/', true));
	}
}