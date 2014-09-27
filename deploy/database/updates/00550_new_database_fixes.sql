# Fix to Gordo - using Unique instead of button special
# None of Gordo's dice can be the same size
UPDATE button SET recipe='(V) (W) (X) (Y) (Z)' WHERE name='Gordo';
UPDATE button SET btn_special=1 WHERE name='Gordo';

# Billy Shakespeare was mispelled as Billy Shakespear
UPDATE button SET name='Billy Shakespeare' WHERE name='Billy Shakespear';

# The Las Vegas Magician is now being listed by his stage name instead of occupation
UPDATE button SET name='Crypto' WHERE name='Magician';
