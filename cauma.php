<?php

/**
 * @author NTI FURG
 */
class cauma extends rcube_plugin{
	private $cache;
	private $rcmail;

	public function init(){
		// Captura Hook
		$this->rcmail = rcmail::get_instance();
		if ($this->rcmail->action == 'preview' || $this->rcmail->action == 'show'){
			$this->include_stylesheet('styles/cauma.css');
			
			$this->add_hook('message_objects', array( 
				$this,
				'verify' 
			));
		}
	}

	/**
	 *
	 * @param array $args
	 *        	array(
	 *        	content: Array com os conteúdos das box de alertas
	 *        	message: The according rcube_message instance
	 *        	)
	 *        	
	 * @return $args
	 */
	public function verify($args){
		ob_start();
		$this->rcmail->storage->print_raw_body($args['message']->uid, false);
		$email = ob_get_clean();

		$links = $this->getLinks($email);

		if (count($links) == 0)
			return $args;
		
		if (null == $this->cache)
			$this->cache = rcube::get_instance()->get_cache_shared('cauma', false);
		
		$iTime = microtime(true);
		$data = date('Y-m-d H:i:s');
		
		$count = 0;
		foreach($links as $link){
			$hash = md5($link);
			$problem = $this->cache === null ? null : $this->cache->get($hash);
			
			if ($problem === null){
				$count++;
				$problem = $this->hasProblem($link);
				if ($problem === null){
					$args['content'][] = $this->getMessage('<strong>Atenção</strong>, não foi possível verificar a confiabilidade dos links do e-mail.', 'warning');
					break;
				}
				error_log("{$data} URL " . ($problem == 1? 'block': 'ok') . " {$link}\n", 3, '/var/log/cauma.log');
				if ($this->cache !== null)
					$this->cache->set($hash, $problem);
			}
			
			if ($problem == 1){
				$args['content'][] = $this->getMessageWithLock("O e-mail contém um link indicado como fraudulênto segundo o serviço <a href='https://cauma.pop-ba.rnp.br/about' target='_blank'>CaUMa</a>: <br /> - {$link}");
				break;
			}
		}
		
		if ($count > 0)
			error_log("{$data} Tempo " . (int) (microtime(true) - $iTime) . " {$count}\n", 3, '/var/log/cauma.log');
		
		return $args;
	}

	private function hasProblem($url){
		$ch = curl_init();
		curl_setopt_array($ch, array( 
			CURLOPT_URL => "https://cauma.pop-ba.rnp.br/api/v1.0/diagnostic/site=" . $url,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_HEADER => false,
			CURLOPT_NOBODY => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_TIMEOUT => 5 
		));
		curl_exec($ch);
		$info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		switch($info){
			case 200:
				return 1;
			case 404:
				return 2;
			default:
				return null;
		}
	}

	private function getLinks($mail){
		preg_match_all('/((http|ftp)s?:)?\/\/([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-])?/i', $mail, $m);
		return array_unique($m[0]);
	}

	private function getMessage($msg, $tipo){
		return '<div class="cauma message ' . $tipo . '">' . $msg . '</div>';
	}

	private function getMessageWithLock($msg){
		$this->include_script('scripts/cauma.js');
		return '<div class="caumaMsgLock">
			<h2>Atenção!</h2>
			' . $msg . '<br />
			<div class="buttons">
				<a href="#" class="remover button">Remover esta mensagem de e-mail</a>
				<a href="#" class="vermesmoassim button">Entendo os riscos e desejo visualizar a mensagem</a>
			</div>
		</div>';
	}
}