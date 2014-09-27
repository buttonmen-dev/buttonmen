# The extra SELECT is to avoid referencing Zoe by the UTF name
SELECT @setid := id FROM buttonset WHERE name="Sluggy Freelance";
UPDATE button SET name='Zoe' WHERE set_id=@setid AND recipe='(4/12) (6/10) (8) (10/20) (12/20)';
