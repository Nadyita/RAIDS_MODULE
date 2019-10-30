<?php

namespace Budabot\User\Modules;

/**
 * Authors:
 *  - Nadyita (RK5)
 *
 * @Instance
 *
 * Commands this controller contains:
 *  @DefineCommand(
 *		command     = 'raids',
 *		accessLevel = 'guild',
 *		description = 'query upcoming raids',
 *		help        = 'raids.txt'
 *	)
 *
 *  @DefineCommand(
 *		command     = 'updateraids',
 *		accessLevel = 'guild',
 *		description = 'update upcoming raids from raidbot',
 *		help        = 'raids.txt'
 *	)
 *
 *  @DefineCommand(
 *		command     = '<font',
 *		accessLevel = 'all',
 *		description = 'update upcoming raids by raidbot'
 *	)
 */
class RaidsController {

	/**
	 * Name of the module.
	 * Set automatically by module loader.
	 */
	public $moduleName;

	/** @Inject */
	public $db;
	
	/** @Inject */
	public $chatBot;
	
	/** @Inject */
	public $settingManager;

	/** @Inject */
	public $text;
	
	/** @Inject */
	public $util;
	
	/** @Inject */
	public $altsController;
	
	/** @Inject */
	public $preferences;
	
	/** @Inject */
	public $playerManager;
	
	/** @Inject */
	public $commandAlias;
	
	/** @Logger */
	public $logger;

	public $raids;

	public $updater;

	/** @Setup */
	public function setup() {
		$this->settingManager->add($this->moduleName, 'raidbot', "Bot where to get the raids information from", "edit", "options", "allianceraid", "allianceraid;hodorraid;hellcom");
		$this->settingManager->add($this->moduleName, 'raids_command', "Which command to send to get a list of raids", "edit", "options", "!raids", "!raids;!news");
	}
	
	/**
	 * @HandlesCommand("raids")
	 */
	public function raidsCommand($message, $channel, $sender, $sendto, $args) {
		if ($this->raids) {
			$sendto->reply($this->raids);
		} else {
			$this->updateRaids($sendto);
		}
	}

	/**
	 * @HandlesCommand("updateraids")
	 */
	public function updateRaidsCommand($message, $channel, $sender, $sendto, $args) {
		$this->updateRaids($sendto);
	}

	public function updateRaids($sendto) {
		$raidBot = $this->settingManager->get('raidbot');
		$message = $this->settingManager->get('raids_command');;

		$sendto->reply("Updating raids from $raidBot...");

		// we use the aochat methods so the bot doesn't prepend default colors
		$this->updater = $sendto;
		$this->chatBot->send_tell($raidBot, $message);

		// manual logging is only needed for tell relay
		$this->logger->logChat("Out. Msg.", $raidBot, $message);
	}

	/**
	 * @HandlesCommand("<font")
	 * @Matches("/last updated/i")
	 */
	public function updateRaidsReply($message, $channel, $sender, $sendto, $args) {
		if ($sender == ucfirst(strtolower($this->settingManager->get('raidbot'))) && preg_match("/last updated/is", $message, $arr)) {
			$this->raids = $message;
			if ($this->updater) {
				$sendto = $this->updater;
				$this->updater = null;
				$sendto->reply($message);
			} else {
				$this->chatBot->sendGuild($message, true);
				$this->chatBot->sendPrivate($message, true);
			}
		}
	}
}

