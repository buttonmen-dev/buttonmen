<?php
/**
 * BMFlagHasJustShrunk: Used to signal that a die has just shrunk
 *
 * @author: james
 */

/**
 * This class is a flag that signals that a die has just decreased its number of sides.
 * It stores the die recipe present before shrinking, in case the die changes again afterwards.
 */
class BMFlagHasJustShrunk extends BMFlagHasJustGrownOrShrunk {

}
