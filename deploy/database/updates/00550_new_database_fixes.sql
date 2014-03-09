# Fix to Gordo - using Unique instead of button special
# None of Gordo's dice can be the same size
UPDATE button SET recipe='(V) (W) (X) (Y) (Z)' WHERE name='Gordo';
UPDATE button SET btn_special=1 WHERE name='Gordo';

# Billy Shakespeare was mispelled as Billy Shakespear
UPDATE button SET name='Billy Shakespeare' WHERE set_id="2004 Origins" AND recipe='(15) (64) (16) (16)';

# The Las Vegas Magician is now being listed by his stage name instead of occupation
UPDATE button SET name='Crypto' WHERE set_id="Las Vegas" AND recipe='(6) (8) (10) (12) (T)';

# NOTE: I'm not sure if I could use name in the condition when I'm updating the name column
#       I might have done this the long way