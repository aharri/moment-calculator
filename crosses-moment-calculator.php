<?php

# Beam end moment balancer based on Cross' method.

# Copyright (c) 2013 Antti Harri <iku@openbsd.fi>
#
# Permission to use, copy, modify, and distribute this software for any
# purpose with or without fee is hereby granted, provided that the above
# copyright notice and this permission notice appear in all copies.
#
# THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
# WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
# MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
# ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
# WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
# ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
# OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.

define('DECIMALS', "10.4");
define('PRECISION', 0.01);

# Which corners to balance?
$balance_corners = array(2,4,6);
# Corners
$corners = array(
	# Corner number 1 has one beam connected that goes to corner 2
	1 => array(
		# Beam ends for corner
		2 => array(
			# Transfer values to 2,1 ?
			'xfer'	=> false,
			# Multiplier. Sum of multipliers in one corner must be 1,
			# except for corners that are not listed in $balance_corners.
			'div'	=> 0,
			# Starting moment
			'moment'=> array(0),
			# Internal, don't change
			'printable'=>array()
		)
	),
	2 => array(
		1 => array(
			'xfer'	=> true,
			'div'	=> 0.142300195,
			'moment'=> array(0),
			'printable'=>array()
		),
		4 => array(
			'xfer'	=> true,
			'div'	=> 0.8567998051,
			'moment'=> array(-119.9025),
			'printable'=>array()
		)
	),
	3 => array(
		4 => array(
			'xfer'	=> false,
			'div'	=> 0,
			'moment'=> array(0),
			'printable'=>array()
		)
	),
	4 => array(
		2 => array(
			'xfer'	=> true,
			'div'	=> 0.551898678,
			'moment'=> array(+119.9025),
			'printable'=>array()
		),
		3 => array(
			'xfer'	=> true,
			'div'	=> 0.091565008,
			'moment'=> array(0),
			'printable'=>array()
		),
		6 => array(
			'xfer'	=> true,
			'div'	=> 0.356536314,
			'moment'=> array(-496.5495),
			'printable'=>array()
		)
	),
	5 => array(
		6 => array(
			'xfer'	=> true,
			'div'	=> 0,
			'moment'=> array(0),
			'printable'=>array()
		)
	),
	6 => array(
		4 => array(
			'xfer'	=> true,
			'div'	=> 0.838494521,
			'moment'=> array(393.3210),
			'printable'=>array()
		),
		5 => array(
			'xfer'	=> false,
			'div'	=> 0.161505479,
			'moment'=> array(0),
			'printable'=>array()
		)
	)

);

while (true)
{
	$high_crn = 0;
	$high_blc = false;
	foreach ($corners as $corner => $beam_ends) {
		$balance = 0;
		foreach ($beam_ends as $beam_end => $data) {
			$balance += array_sum($data['moment']);
		}
		printf ("Corner balance of {$corner}: $balance\n");
		if (array_search($corner, $balance_corners) === false)
			continue;
		if (abs($high_blc) < abs($balance) || !$high_blc) {
			$high_crn = $corner;
			$high_blc = $balance;
			printf ("Switching to corner $corner\n");
		}
	}
	if (abs($high_blc) <= PRECISION) {
		printf ("Balance mismatch less than %f -> breaking\n", PRECISION);
		break;
	}
	printf ("Selected corner $high_crn\n");
	$beam_ends = $corners[$high_crn];
	foreach ($beam_ends as $beam_end => $data) {
		$i = $high_blc * $data['div'] * -1;
		printf ("Beam end {$high_crn},{$beam_end} = ".array_sum($data['moment'])." + $i\n");
		$corners[$high_crn][$beam_end]['moment'][] = $i;
		$corners[$high_crn][$beam_end]['printable'][] = array('type'=>'bal', 'val'=>$i);
		if ($data['xfer']) {
			$c = $beam_end;
			$be = $high_crn;
			printf ("Transferring ".($i*0.5)." to {$c},{$be}\n");
			$corners[$c][$be]['moment'][] = $i * 0.5;
			$corners[$c][$be]['printable'][] = array('type'=>'xfer', 'val'=>$i * 0.5);
		}
	}
}

foreach ($corners as $corner => $beam_ends) {
	printf("Corner $corner:\n");
	foreach ($beam_ends as $beam_end => $data) {
		printf("Bending moment for $corner,$beam_end:\n");
		$sum = $data['moment'][0];
		printf ("\t%".DECIMALS."f\n", $sum);
		foreach ($data['printable'] as $moment) {
			printf ("\t%".DECIMALS."f%s\n", $moment['val'], $moment['type']=='bal'?'*':'');
			$sum += $moment['val'];
		}
		printf ("\nSum:\t%".DECIMALS."f\n\n", $sum);
	}
}

?>
