# change all of Fightball set to indicate that they use button specials
UPDATE button SET btn_special=1 WHERE set_id=(SELECT id FROM buttonset WHERE name="Fightball");