<?php
/*
 * Plugin Name: Who Repped My Post? for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains data used by classes/installer.php
 */

$settings = array(
	'wrmp_settings' => array(
		'group' => array(
			'name' => 'wrmp_settings',
			'title' => $lang->wrmp,
			'description' => $lang->wrmp_settingsgroup_description,
			'disporder' => '107',
			'isdefault' => 0
		),
		'settings' => array(
			'wrmp_position' => array(
				'sid' => 'NULL',
				'name' => 'wrmp_position',
				'title' => $lang->wrmp_position_title,
				'description' => $lang->wrmp_position_desc,
				'optionscode' => <<<EOF
select
postbit={$lang->wrmp_position_postbit}
post={$lang->wrmp_position_post}
below={$lang->wrmp_position_below}
EOF
				,
				'value' => 'below',
				'disporder' => '10'
			),
			'wrmp_show_negative' => array(
				'sid' => 'NULL',
				'name' => 'wrmp_show_negative',
				'title' => $lang->wrmp_show_negative_title,
				'description' => $lang->wrmp_show_negative_desc,
				'optionscode' => 'yesno',
				'value' => '1',
				'disporder' => '20'
			),
			'wrmp_max_negative' => array(
				'sid' => 'NULL',
				'name' => 'wrmp_max_negative',
				'title' => $lang->wrmp_max_negative_title,
				'description' => $lang->wrmp_max_negative_desc,
				'optionscode' => 'text',
				'value' => '3',
				'disporder' => '30'
			),
			'wrmp_show_neutral' => array(
				'sid' => 'NULL',
				'name' => 'wrmp_show_neutral',
				'title' => $lang->wrmp_show_neutral_title,
				'description' => $lang->wrmp_show_neutral_desc,
				'optionscode' => 'yesno',
				'value' => '1',
				'disporder' => '40'
			),
			'wrmp_max_neutral' => array(
				'sid' => 'NULL',
				'name' => 'wrmp_max_neutral',
				'title' => $lang->wrmp_max_neutral_title,
				'description' => $lang->wrmp_max_neutral_desc,
				'optionscode' => 'text',
				'value' => '3',
				'disporder' => '50'
			),
			'wrmp_show_positive' => array(
				'sid' => 'NULL',
				'name' => 'wrmp_show_positive',
				'title' => $lang->wrmp_show_positive_title,
				'description' => $lang->wrmp_show_positive_desc,
				'optionscode' => 'yesno',
				'value' => '1',
				'disporder' => '60'
			),
			'wrmp_max_positive' => array(
				'sid' => 'NULL',
				'name' => 'wrmp_max_positive',
				'title' => $lang->wrmp_max_positive_title,
				'description' => $lang->wrmp_max_positive_desc,
				'optionscode' => 'text',
				'value' => '3',
				'disporder' => '70'
			),
		)
	)
);

$templates = array(
	'wrmp' => array(
		'group' => array(
			'prefix' => 'wrmp',
			'title' => $lang->wrmp,
		),
		'templates' => array(
			'wrmp_reps_negative' => <<<EOF
{\$sep}<span style="padding: 5px;"><img style="position: relative; top: 3px;" src="{\$theme['imgdir']}/wrmp/negative.png" alt="{\$lang->wrmp_negative}" title="{\$lang->wrmp_negative_reps}"/>&nbsp;{\$whoReppedMe}{\$otherReps}</span>
EOF
			,
			'wrmp_reps_neutral' => <<<EOF
{\$sep}<span style="padding: 5px;"><img style="position: relative; top: 3px;" src="{\$theme['imgdir']}/wrmp/neutral.png" alt="{\$lang->wrmp_neutral}" title="{\$lang->wrmp_neutral_reps}"/>&nbsp;{\$whoReppedMe}{\$otherReps}</span>
EOF
			,
			'wrmp_reps_positive' => <<<EOF
{\$sep}<span style="padding: 5px;"><img style="position: relative; top: 3px;" src="{\$theme['imgdir']}/wrmp/positive.png" alt="{\$lang->wrmp_positive}" title="{\$lang->wrmp_positive_reps}"/>&nbsp;{\$whoReppedMe}{\$otherReps}</span>
EOF
			,
			'wrmp_postbit' => <<<EOF
<td class="smalltext" style="width: 350px;">{\$wrmp[\$pid]}</td><td style="width: 250px;"></td>
EOF
			,
			'wrmp_postbit_classic' => <<<EOF
<div style="white-space: normal; width: 150px; max-width: 150px; margin-top: 10px;">{\$wrmp[\$pid]}</div>
EOF
			,
			'wrmp_post' => <<<EOF
<fieldset class="smalltext" style="width: 200px; float: right; border: solid 1px lightgrey; color: grey;"><legend><strong>{\$lang->wrmp_reps}</strong></legend>{\$wrmp[\$pid]}</fieldset>
EOF
			,
			'wrmp_below' => <<<EOF
<span class="smalltext" style="width: 150px;">{\$wrmp[\$pid]}</span>
EOF
			,
			'wrmp_other_reps' => <<<EOF
 {\$lang->and} {\$over_count} <span title="{\$othersList}">{\$other}</span>
EOF
			,
			'wrmp_user_link' => <<<EOF
<a href="{\$userLink}" title="{\$user['repvalue']}{\$comments}">{\$userName}</a>
EOF
		),
	),
);

$images = array(
	'folder' => 'wrmp',
	'forum' => array(
		'negative.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB94FBAUGBYOZXjoAAAAMaVRYdENvbW1lbnQAAAAAALyuspkAAAGASURBVDjLrZJNaxpRFIaf09gieEVHRzEorgbS7FIoXXRdBg35A11kYdbuxp/jP+g2JCDdZJlNdqUp6DoQGGsG3OiMJ5u5g5kybaF94cK59/K8nC/4R0n+YTKZfCiXy5P1eu3HcVxrtVo4jvM0Ho8/AzMRSQoNgiC4Wy6XJ8aY7L3dbmf/3W5XR6PRsYj8+MUgCIK7zWbzLg8BdDqdLO71egwGg7fWpGTTDsPwxBiTwfuQBa0Wi8V3VX0tIkkJQESujDGSh/chAM/zAGg2mwL4wHUJYLvdNopgCwG4rku9XrfXi8yg0WgIwMXtLXEUFY4sSk//5gbgU9YDqziKeHN//wL6lutFmCQ8Xl7y/uyMzMBxnKfValWbnZ/DwwOdw0Oq1SqVSoVaLou9+9dsjKo6nE6nV7Z2z/Ne1F6gUxG5tiXM+v2+7nY7+csNXgIzgFfpGBPf949t113X/ZPBx/xKk5ZyNJ/Pd1qsUFWPfmutqgeqOlTVLyn0M42HqnrA/9YzDeKldglU6UMAAAAASUVORK5CYII=
EOF
		),
		'neutral.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB94FBAUHG2CNUhgAAAAMaVRYdENvbW1lbnQAAAAAALyuspkAAAGVSURBVDjLrZK9btpgFIafjxQJIUvB/FhIGIRkS7RbuvQKoggugKEjkdjYzMDIwMbI6IEL6EwtoV5D1qoCDwwMDIAysBjSkyFgOQ4kQ/tOR8/R+55zPn3wj1Jx0O12v6VSqe5ut7s7HA7XhUIBXdcfO53Od2CqlHq6GOA4zsNms7nRNC3khmGE/VKpJK1W64tS6s+bAMdxHoIg+Bo3ARSLxbA2TZN6vf75FPLptPZ6vb7RNI3RaASAZVn0ej0A2u02AJ7nAeD7/m8RSSqlnhIASqmfmqap6GTf91ksFpimGTLbtrFtm1wup4A7gATAfr/PxtdOp9OMx2Oq1WrI8vk8tm2TyWQA7sOAbDar4rc2m02WyyWTySRkR+NJt2HAOTmOg2EYDIfDd/9BAkDX9cd4o1wu0+/3Wa1Wl7y/wkpEGq7riuu6AsgLEgmCQCzLesUiakRPmFYqFYm+OEAymWQwGJybvgGmr4iI1DzPk9lsJtvtVj5Q7exRIlKbz+d/3zGuL5ojIVci0hCRH0fT9lg3ROSK/61nIcbpkJoRcXEAAAAASUVORK5CYII=
EOF
		),
		'positive.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB94FBA0GK1FcAk0AAAHISURBVDjLrZM9iBNBGIaf2dvsRd0z2fyxJGtAWMjZnRYeFhaiHDkQrCyujI3Ndhu0VrH1wM4r7KyuNxBiLGy9FDYiSRCxlGxyRZok5rO47JKsl0pfGBi+meedd5hv4B+l4oV6vX4zmUzWx+Px3mw2S+XzeSzLOvU87wBoKqV+rzXwfb8TBMGOaZpRvVAoROulUklqtdo1pdS3vwx83+9MJpPrcQjAtu1o7jgO1Wp1OzTRwthBEOwsw7Zt86b7iqPvhxHoOA4A/X7/q4hsRAZKqfemaaplGEDpiotXDYbzAQCu6+K6LtlsVgF7ADrAdDrNxGHHcVD6WexisYjruuRyOdLpdHibR0BDB8hkMiqEn316gtIVSgdlKPQtjdc/XmL8NEhoBu3Hn0ODe9EVVp5lEfvCFQPT3eSSvoWhncEJzeBj58PKfh3AsqzT0WiUAjh6+I7B7Bep1GVefHmKoRk83z08r4daUQLP8w6Wn2p3+xb3bz+ITga4c+NuNBZ6GyUAmuVyWebz+UpjJZYMYgqA5kpFRCqNRkO63a4Mh0MJ1T5pSfukJTFVzv0YIlLp9XpzWa/BWnjJZENE9kXkeAENF/P9sPv+q/4A+1TLHa9d8rgAAAAASUVORK5CYII=
EOF
		),
	),
	'acp' => array(
		'donate.gif' => array(
			'image' => <<<EOF
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
EOF
		),
		'pixel.gif' => array(
			'image' => <<<EOF
R0lGODlhAQABAID/AMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==
EOF
		),
		'settings.gif' => array(
			'image' => <<<EOF
R0lGODlhEAAQAOMLAAAAAAMDAwYGBgoKCg0NDRoaGh0dHUlJSVhYWIeHh5aWlv///////////////////yH5BAEKAA8ALAAAAAAQABAAAARe8Mn5lKJ4nqRMOtmDPBvQAZ+IIQZgtoAxUodsEKcNSqXd2ahdwlWQWVgDV6JiaDYVi4VlSq1Gf87L0GVUsARK3tBm6LAAu4ktUC6yMueYgjubjHrzVJ2WKKdCFBYhEQA7
EOF
		),
	),
);

?>
