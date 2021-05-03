<?php

namespace HannesTheDev\JoinQuitMessage;

use DateTime;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener
{

    public $message;
    public $prefix;
    public $colored = array(
        "§0",
        "§1",
        "§2",
        "§3",
        "§4",
        "§5",
        "§6",
        "§7",
        "§8",
        "§9",
        "§e",
        "§a",
        "§o",
        "§l",
        "§r",
        "§d",
        "§c",
        "§b",
        "§f",
        "&0",
        "&1",
        "&2",
        "&3",
        "&4",
        "&5",
        "&6",
        "&7",
        "&8",
        "&9",
        "&e",
        "&a",
        "&o",
        "&l",
        "&r",
        "&d",
        "&c",
        "&b",
        "&f"
    );

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource("messages.yml");
        $this->message = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        $this->prefix = $this->message->get("prefix");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case "jqm":
                if ($sender instanceof Player) {
                    $this->openJoinQuit($sender);
                }
                break;
        }
        return true;
    }

    public function openJoinQuit(Player $player)
    {
        $form = new SimpleForm(function(Player $player, $data = null) {
            $result = $data;
            if ($result === null) {
                $player->sendMessage($this->prefix . $this->message->get("operation-cancelled"));
                return true;
            }
            switch ($data) {
                case 0:
                    $this->checkJoin($player);
                    break;
                case 1:
                    $this->checkQuit($player);
                    break;
                case 2:
                    break;
            }
        });
        $form->setTitle($this->message->get("1-title"));
        $form->setContent($this->message->get("1-content"));
        $form->addButton($this->message->get("1-button-1"));
        $form->addButton($this->message->get("1-button-2"));
        $form->addButton($this->message->get("1-button-3"));
        $form->sendToPlayer($player);
        return $form;
    }

    public function checkJoin(Player $player)
    {
        if ($player->hasPermission("jqm.join.cmd")) {
            $this->openJoin($player);
        } else {
            $player->sendMessage($this->prefix . $this->message->get("no-permission"));
        }
    }

    public function checkQuit(Player $player)
    {
        if ($player->hasPermission("jqm.quit.cmd")) {
            $this->openQuit($player);
        } else {
            $player->sendMessage($this->prefix . $this->message->get("no-permission"));
        }
    }

    public function openJoin(Player $player) {
        $form = new CustomForm(function(Player $player, $data = null) {
            $result = $data;
            if ($result === null) {
                $player->sendMessage($this->prefix . $this->message->get("operation-cancelled"));
                return true;
            }
            if (!empty($data[0])) {
                $cd = new Config($this->getDataFolder() . "joincooldown.yml", Config::YAML);
                if (!$cd->exists($player->getName())) {
                    $cd->set($player->getName(), date('Y-m-d H:i:s'));
                    $cd->save();
                }
                $last = new DateTime($cd->get($player->getName()));
                if (!$player->hasPermission("jqm.pass.cooldown")) {
                    if (new DateTime("now") >= $last) {
                        $nachricht = $data[0];
                        $config = new Config($this->getDataFolder() . "joinquitmessages.yml", Config::YAML);
                        $config->set("joinmessages." . $player->getName(), $nachricht);
                        $config->save();
                        $messages = $this->message->get("successful-join");
                        $messages = str_replace('{message}', $nachricht, $messages);
                        $player->sendMessage($this->prefix . $messages);
                        $date = new DateTime('+' . $this->message->get("cooldown-minutes") . ' minutes');
                        $cd->set($player->getName(), $date->format("Y-m-d H:i:s"));
                        $cd->save();
                    } else {
                        $waiting = $this->message->get("join-wait-message");
                        $waiting = str_replace('{time}', $cd->get($player->getName()), $waiting);
                        $player->sendMessage($this->prefix . $waiting);
                    }
                } else {
                    $nachricht = $data[0];
                    $config = new Config($this->getDataFolder() . "joinquitmessages.yml", Config::YAML);
                    $config->set("joinmessages." . $player->getName(), $nachricht);
                    $config->save();
                    $messages = $this->message->get("successful-join");
                    $messages = str_replace('{message}', $nachricht, $messages);
                    $player->sendMessage($this->prefix . $messages);
                }
            } else {
                $player->sendMessage($this->prefix . $this->message->get("operation-cancelled"));
            }
        });
        $form->setTitle($this->message->get("2-title"));
        $form->addInput($this->message->get("2-input"));
        $form->sendToPlayer($player);
        return $form;
    }

    public function openQuit(Player $player) {
        $form = new CustomForm(function(Player $player, $data = null) {
            $result = $data;
            if ($result === null) {
                $player->sendMessage($this->prefix . $this->message->get("operation-cancelled"));
                return true;
            }
            if (!empty($data[0])) {
                $cd = new Config($this->getDataFolder() . "quitcooldown.yml", Config::YAML);
                if (!$cd->exists($player->getName())) {
                    $cd->set($player->getName(), date('Y-m-d H:i:s'));
                    $cd->save();
                }
                $last = new DateTime($cd->get($player->getName()));
                if (!$player->hasPermission("jqm.pass.cooldown")) {
                    if (new DateTime("now") >= $last) {
                        $nachricht = $data[0];
                        $config = new Config($this->getDataFolder() . "joinquitmessages.yml", Config::YAML);
                        $config->set("quidmessages." . $player->getName(), $nachricht);
                        $config->save();
                        $messages = $this->message->get("successful-quit");
                        $messages = str_replace('{message}', $nachricht, $messages);
                        $player->sendMessage($this->prefix . $messages);
                        $date = new DateTime('+' . $this->message->get("cooldown-minutes") . ' minutes');
                        $cd->set($player->getName(), $date->format("Y-m-d H:i:s"));
                        $cd->save();
                    } else {
                        $waiting = $this->message->get("quit-wait-message");
                        $waiting = str_replace('{time}', $cd->get($player->getName()), $waiting);
                        $player->sendMessage($this->prefix . $waiting);
                    }
                } else {
                    $nachricht = $data[0];
                    $config = new Config($this->getDataFolder() . "joinquitmessages.yml", Config::YAML);
                    $config->set("quidmessages." . $player->getName(), $nachricht);
                    $config->save();
                    $messages = $this->message->get("successful-quit");
                    $messages = str_replace('{message}', $nachricht, $messages);
                    $player->sendMessage($this->prefix . $messages);
                }
            } else {
                $player->sendMessage($this->prefix . $this->message->get("operation-cancelled"));
            }
        });
        $form->setTitle($this->message->get("3-title"));
        $form->addInput($this->message->get("3-input"));
        $form->sendToPlayer($player);
        return $form;
    }

    public function PlayerJoinEvent(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $config = new Config($this->getDataFolder() . "joinquitmessages.yml", Config::YAML);
        if ($config->exists("joinmessages." . $player->getName())) {
            $nachricht = $config->get("joinmessages." . $player->getName());
            if ($player->hasPermission("jqn.colored.message")) {
                $event->setJoinMessage("§8[§a+§8] §r" . $nachricht);
            } else {
                $nachricht = $this->replaceWords($nachricht);
                $event->setJoinMessage("§8[§a+§8] §6" . $nachricht);
            }
        } else {
            $event->setJoinMessage("§8[§a+§8] §7" . $player->getName());
        }
    }

    public function PlayerQuitEvent(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        $config = new Config($this->getDataFolder() . "joinquitmessages.yml", Config::YAML);
        if ($config->exists("quidmessages." . $player->getName())) {
            $nachricht = $config->get("quidmessages." . $player->getName());
            if ($player->hasPermission("jqn.colored.message")) {
                $event->setQuitMessage("§8[§c-§8] §r" . $nachricht);
            } else {
                $nachricht = $this->replaceWords($nachricht);
                $event->setQuitMessage("§8[§c-§8] §6" . $nachricht);
            }
        } else {
            $event->setQuitMessage("§8[§c-§8] §7" . $player->getName());
        }
    }

    public function replaceWords($nachricht)
    {
        $count = count($this->colored);
        $nachricht = strtolower($nachricht);
        for ($i = 0; $i < $count; $i++) {
            $nachricht = str_replace($this->colored[$i], str_repeat("", strlen($this->colored[$i])), $nachricht);
        }
        return $nachricht;
    }
}
