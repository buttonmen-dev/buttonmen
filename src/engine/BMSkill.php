<?php

class BMSKill
{
	
}

class BMSkillShadow extends BMSkill
{
	public $name = "Shadow";

	public static function attack_list($args)
	{
		$list = $args[0];

		foreach ($list as $i => $att)
		{
			if ($att == "Power")
			{
				unset($list[$i]);
			}
		}

		$list[] = "Shadow";
	}
}

?>
