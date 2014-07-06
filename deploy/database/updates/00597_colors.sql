ALTER TABLE player
  ADD COLUMN player_color VARCHAR(7) NULL AFTER comment,
  ADD COLUMN opponent_color VARCHAR(7) NULL AFTER player_color,
  ADD COLUMN neutral_color_a VARCHAR(7) NULL AFTER opponent_color,
  ADD COLUMN neutral_color_b VARCHAR(7) NULL AFTER neutral_color_a;
