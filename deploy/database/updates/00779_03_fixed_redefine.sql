UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a vanilla random formula: 5 dice, no swing dice, no skills'
WHERE name='RandomBMVanilla';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a fixed random formula: 5 dice, no swing dice, two of them having a single skill chosen from c, f, and d (the same skill on both)'
WHERE name='RandomBMFixed';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a fixed random formula: 5 dice, no swing dice, three skills chosen from all existing skills, with each skill dealt out twice randomly and independently over all dice)'
WHERE name='RandomBMMixed';
