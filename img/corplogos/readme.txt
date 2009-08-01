EVE Online Prerendered Corplogos
Rendered by Entity <jamie@hlekkir.com>

LICENSE:
These logos may be used for non-profit purposes only.
(in-game ISK is fine, though)

DISCLAIMER:
LONG-WINDED ALL-CAPS BLURB OF TEXT THAT NOBODY READS ANYWAY BUT SEEMS TO BE
NECCESSARY, SO HERE IT IS: USE THESE LOGOS AT YOUR OWN RISK.

USAGE:
These are transparent renders of each individual logo image, for each of the
12 possible color shaders. Most image processing applications will produce
a properly looking corp logo by simply pasting the image for each layer onto
the target image. However, to produce a transparent composite image for use
on websites or further processing, an extra step is required after rendering
the three layers:

For each pixel in composed image:
  final_alpha = 1 - (1 - layer1_alpha) * (1 - layer2_alpha) * (1 - layer3_alpha)
  final_color = image_color / final_alpha


ACKNOWLEDGEMENTS:
EVE Online, EVE and all associated logos and designs are the intellectual
property of CCP hf.

