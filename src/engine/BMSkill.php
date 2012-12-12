<?php

/*
 * BMSkill: Used to modify the operation of BMDie
 *
 * @author: Julian Lighton
 */

class BMSKill
{
	
}

class BMSkillShadow extends BMSkill
{
	public static $name = "Shadow";

    public static $abbrev = "s";

    public static $hooked_methods = array("attack_list");

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
