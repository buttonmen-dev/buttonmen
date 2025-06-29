INSERT into button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
 (868, 'xmbrst', 'dk(1) dk(2) (13) (R) (R)!', 0, 0, (SELECT id FROM buttonset WHERE name="Modern Fanatics"));
UPDATE button SET flavor_text="Xmbrst is an itinerant theremin player who was raised by an anarcho-syndicalist collective onboard a lime-green submarine. He likes word machines, genre calendars, and beating people up." WHERE name="xmbrst";
