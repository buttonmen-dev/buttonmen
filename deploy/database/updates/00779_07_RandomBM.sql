START TRANSACTION;

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with four regular dice and one swing die, and 2 skills each appearing a total of 2 times on various dice.'
WHERE name='RandomBMDuoskill';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with four regular dice and one swing die, and 3 skills each appearing a total of 2 times on various dice.'
WHERE name='RandomBMTriskill';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with four regular dice and one swing die, and 4 skills each appearing a total of 2 times on various dice.'
WHERE name='RandomBMTetraskill';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with four regular dice and one swing die, and 5 skills each appearing a total of 2 times on various dice.'
WHERE name='RandomBMPentaskill';

COMMIT;
