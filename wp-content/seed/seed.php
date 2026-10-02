<?php

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

if ( get_stylesheet() !== 'wealth' ) {
	switch_theme( 'wealth' );
	echo "theme: switched to wealth\n";
}

if ( ! function_exists( 'wealth_setup' ) ) {
	require_once get_theme_file_path( 'functions.php' );
	wealth_setup();
}

function wealth_seed_media_signature(): string {
	return md5( wp_json_encode( [ wp_get_registered_image_subsizes(), wp_image_editor_supports( [ 'mime_type' => 'image/webp' ] ) ] ) );
}

function wealth_seed_attachment( $filename, $alt ) {
	$existing = get_posts( [
		'meta_key'    => '_wealth_seed_file',
		'meta_value'  => $filename,
		'numberposts' => 1,
		'post_status' => 'any',
		'post_type'   => 'attachment',
	] );

	$signature = wealth_seed_media_signature();

	if ( $existing && get_post_meta( $existing[0]->ID, '_wealth_seed_signature', true ) === $signature ) {
		$id = (int) $existing[0]->ID;
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
		return $id;
	}

	if ( $existing ) {
		wp_delete_attachment( $existing[0]->ID, true );
		echo "media REGENERATING {$filename}\n";
	}

	$source = __DIR__ . '/img/' . $filename;

	if ( ! file_exists( $source ) ) {
		echo "media MISSING {$filename}\n";
		return 0;
	}

	$tmp = wp_tempnam( $filename );
	copy( $source, $tmp );

	$id = media_handle_sideload( [ 'name' => $filename, 'tmp_name' => $tmp ], 0 );

	if ( is_wp_error( $id ) ) {
		@unlink( $tmp );
		echo "media FAILED {$filename}: " . $id->get_error_message() . "\n";
		return 0;
	}

	update_post_meta( $id, '_wealth_seed_file', $filename );
	update_post_meta( $id, '_wealth_seed_signature', $signature );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );

	return (int) $id;
}

function wealth_seed_post( $type, $slug, $args, $meta = [], $thumb = 0 ) {
	$found = get_page_by_path( $slug, OBJECT, $type );
	$args  = array_merge( [ 'post_name' => $slug, 'post_status' => 'publish', 'post_type' => $type ], $args );

	if ( $found ) {
		$args['ID'] = $found->ID;
		$id         = wp_update_post( $args );
		$created    = 0;
	} else {
		$id      = wp_insert_post( $args );
		$created = 1;
	}

	foreach ( $meta as $key => $value ) {
		update_post_meta( $id, $key, $value );
	}

	if ( $thumb ) {
		set_post_thumbnail( $id, $thumb );
	}

	return [ (int) $id, $created ];
}

function wealth_seed_menu( $name, $items ) {
	$menu     = wp_get_nav_menu_object( $name );
	$id       = $menu ? $menu->term_id : wp_create_nav_menu( $name );
	$existing = wp_get_nav_menu_items( $id ) ?: [];

	if ( ! $existing ) {
		foreach ( $items as $item ) {
			wp_update_nav_menu_item( $id, 0, $item + [ 'menu-item-status' => 'publish' ] );
		}

		return $id;
	}

	$by_title = array_column( $existing, null, 'title' );

	foreach ( $items as $item ) {
		$current = $by_title[ $item['menu-item-title'] ] ?? null;

		if ( $current && 'custom' === $item['menu-item-type'] && $current->url !== $item['menu-item-url'] ) {
			wp_update_nav_menu_item( $id, $current->db_id, $item + [ 'menu-item-status' => 'publish' ] );
		}
	}

	return $id;
}

$alts = [
	'hero-escalators.jpg'                           => 'Escalators rising towards the dome of St Paul\'s Cathedral',
	'hero-millennium-bridge.jpg'                    => 'St Paul\'s Cathedral from the Millennium Bridge at dusk',
	'hero-tower-bridge.jpg'                         => 'Tower Bridge lit at night over the Thames',
	'hero-underground.jpg'                          => 'An empty London Underground passage',
	'map-st-james.jpg'                              => "Greyscale street map of St James's, centred on King Street",
	'news-case-for-short-dated-sterling-credit.jpg' => 'A stack of bound ledgers on a wooden desk',
	'news-multi-asset-income-outlook.jpg'           => 'Electricity pylons crossing open farmland at dusk',
	'news-quality-growth-in-europe.jpg'             => 'Precision machine tools in a quiet engineering workshop',
	'news-reading-the-central-banks.jpg'            => 'Stone columns of a classical building in the City of London',
	'news-stewardship-review-2026.jpg'              => 'A long boardroom table set with paper agendas',
	'news-why-income-still-matters.jpg'             => 'A brass letterbox in a black Georgian front door',
	'strategy-european-quality-growth.jpg'          => '',
	'strategy-global-multi-asset-income.jpg'        => '',
	'strategy-short-dated-sterling-credit.jpg'      => '',
	'strategy-uk-equity-income.jpg'                 => '',
	'team-daniel-okafor.jpg'                        => 'Portrait of Daniel Okafor',
	'team-eleanor-whitcombe.jpg'                    => 'Portrait of Eleanor Whitcombe',
	'team-hannah-mercer.jpg'                        => 'Portrait of Hannah Mercer',
	'team-james-hartley.jpg'                        => 'Portrait of James Hartley',
	'team-sophie-lindqvist.jpg'                     => 'Portrait of Sophie Lindqvist',
	'team-tomas-herrera.jpg'                        => 'Portrait of Tomás Herrera',
];

$img = [];
foreach ( $alts as $file => $alt ) {
	$img[ $file ] = wealth_seed_attachment( $file, $alt );
}
echo 'media: ' . count( array_filter( $img ) ) . ' of ' . count( $alts ) . " attachments ready\n";

$created_pages = 0;

$about_body = <<<'HTML'
<p>Wealth Asset Management is an independent, partner-owned investment manager based in St James's, London. We run a small number of focused strategies for pension schemes, insurers, charities, family offices and the wealth managers who advise private clients. We are known for disciplined, research-led investing, for the plainness of our reporting, and for keeping the people who manage your capital in the room when you ask about it.</p>
<p>The firm was founded on a simple view: investors are poorly served by an industry that gathers assets faster than it generates returns. We would rather be the right size than the biggest, and we close strategies when capacity, not demand, says so.</p>
HTML;

$opportunities_body = <<<'HTML'
<p>Research is at the heart of every decision we make. Our four strategies share one process: patient, fundamental analysis of businesses and borrowers, a deliberate concentration in our best ideas, and a long view on income and the compounding of capital.</p>
<p>We believe the shift towards passive and index-driven capital leaves durable mispricings in UK and European equities and in short-dated sterling credit. Each strategy is available as a segregated mandate or through a pooled vehicle for eligible investors, with full transparency of holdings, costs and voting.</p>
HTML;

$legal_content = <<<'HTML'
<h2 id="who-we-are">Who we are</h2>
<p>In this notice, "Wealth", "we" and "us" mean Wealth Asset Management LLP, a limited liability partnership registered in England and Wales with number OC000000. Our registered office is at 14 King Street, St James's, London SW1Y 6QU. You can contact us at enquiries@wealth-am.co.uk or on +44 (0)20 7946 0123.</p>
<h2 id="regulatory-status">Regulatory status</h2>
<p>A real firm offering the services described on this site would need to be authorised and regulated by the Financial Conduct Authority. It would appear on the Financial Services Register with a firm reference number, and it would be bound by the regulator's rules on how it treats clients, holds their money, manages conflicts and handles complaints. Pooled funds of the kind described would also have a depositary and an independent administrator, and would publish a prospectus and key information documents.</p>
<p>Wealth has none of these. It is not authorised, it has no firm reference number, and it holds no client money or assets. The descriptions above explain how a firm of this kind is regulated in general terms. They are not a claim that Wealth is regulated.</p>
<h2 id="who-this-website-is-for">Who this website is for</h2>
<p>The strategies described on this site are presented as suitable only for professional clients and eligible counterparties, as those terms are defined in the regulator's rules. They are not presented for retail investors. The site is not directed at anyone in a country where its content would be unlawful, including the United States.</p>
<p>If you are a private investor, speak to a regulated financial adviser about your own circumstances.</p>
<h2 id="no-advice-or-offer">No advice or offer</h2>
<p>Nothing on this site is investment, legal, tax or accounting advice, and nothing takes account of your objectives, financial situation or needs. Nothing on this site is an offer to buy or sell, or an invitation to apply for, any investment or service. Articles reflect the views of their authors on the date of publication and may change without notice.</p>
<h2 id="risk-warnings">Risk warnings</h2>
<p>The value of investments and the income from them can fall as well as rise, and you may get back less than you invest. Capital is at risk.</p>
<p>Shares can fall sharply and stay low for long periods. Bond issuers can fail to pay interest or repay capital, and bond prices fall when interest rates rise. Concentrated portfolios can behave very differently from the market. Changes in exchange rates can reduce the value of overseas investments. Some investments may be hard to sell quickly at a fair price. Each strategy page sets out its main risks in plain English.</p>
<h2 id="performance">Performance</h2>
<p>Any performance, yield or portfolio figures on this site are illustrative and fictional. Past performance is not a guide to future returns. Where figures are shown in a currency other than your own, exchange-rate changes can increase or reduce returns. Figures do not reflect every cost an investor might pay.</p>
<h2 id="third-party-information">Third-party information</h2>
<p>Index names, including FTSE All-Share, MSCI Europe ex UK and ICE BofA 1–5 Year Sterling Corporate, are trade marks of their owners. They are used here only to describe fictional strategies. Their owners have not reviewed, approved or endorsed this site. Market data and other information from third parties are believed to be reliable, but we cannot vouch for their accuracy or completeness.</p>
<h2 id="liability">Liability</h2>
<p>We take care to keep this site accurate, but we make no representation that its content is complete, current or free from error. To the extent the law allows, we accept no liability for any loss arising from use of this site or reliance on its content. Nothing in this notice limits any liability that cannot be limited by law.</p>
<p>Links to other websites are provided for convenience. We are not responsible for their content.</p>
<h2 id="governing-law">Governing law</h2>
<p>This notice and your use of this site are governed by the law of England and Wales. The courts of England and Wales have exclusive jurisdiction over any dispute arising from them.</p>
<h2 id="complaints">Complaints</h2>
<p>A regulated firm must have a written complaints procedure, acknowledge complaints promptly and give a final response within a set period. Eligible complainants who remain dissatisfied can refer the matter to the Financial Ombudsman Service. Because Wealth is fictional and provides no services, there is nothing to complain about in that sense. If something on this site is wrong or unclear, please tell us at enquiries@wealth-am.co.uk and we will correct it.</p>
<h2 id="conflicts-of-interest">Conflicts of interest</h2>
<p>A firm of this kind must identify and manage conflicts between its own interests and those of its clients, and between one client and another. Our policy, as it would apply, covers four main areas. Staff may not trade personally in securities held by our strategies. Trades are allocated between portfolios fairly, in proportion to each portfolio's intended position. Gifts and hospitality are recorded and kept modest. Partners' own investments are held in the firm's strategies on the same terms as clients'.</p>
HTML;

$privacy_content = <<<'HTML'
<p>Wealth is a fictional firm and this is a demonstration website. This notice describes how the site handles the small amount of personal data it collects. Please do not send real personal or financial information through the site.</p>
<h2 id="who-is-responsible-for-your-data">Who is responsible for your data</h2>
<p>Wealth Asset Management LLP, 14 King Street, St James's, London SW1Y 6QU, is the controller of personal data collected through this website. You can contact us about privacy at enquiries@wealth-am.co.uk. Please put "Privacy" in the subject line.</p>
<h2 id="what-we-collect">What we collect</h2>
<p>When you use the contact form we collect your name and email address, and, if you choose to give them, your organisation and phone number. We also collect your investor type and your message.</p>
<p>When you visit any page, our hosting provider records technical information that every web server receives: your IP address, the page requested, the date and time, and your browser type. We do not use analytics, advertising or tracking tools, and we do not build profiles of visitors.</p>
<h2 id="why-we-use-it-and-on-what-basis">Why we use it, and on what basis</h2>
<ul>
<li><strong>To reply to your enquiry.</strong> Our lawful basis is your consent, which you give with the tick box on the form, and our legitimate interest in answering people who contact us.</li>
<li><strong>To keep the site secure and working.</strong> Our lawful basis is our legitimate interest in protecting the site from misuse. Server logs help us spot and block attacks.</li>
<li><strong>To meet legal obligations.</strong> If the law requires us to keep or disclose information, our lawful basis is compliance with that obligation.</li>
</ul>
<p>We do not use your data for marketing, and we never sell it.</p>
<h2 id="who-we-share-it-with">Who we share it with</h2>
<p>We share personal data only with the providers who run the site for us: our hosting provider, and the email service that delivers form messages to our team. They act on our instructions and may not use your data for their own purposes. We may also disclose data where the law requires it.</p>
<p>Some of these providers may process data outside the UK. Where they do, we rely on a UK adequacy decision or on the UK's approved contract clauses to protect it.</p>
<h2 id="how-long-we-keep-it">How long we keep it</h2>
<ul>
<li>Contact form enquiries: 24 months after our last exchange with you, then deleted.</li>
<li>Server logs: 30 days, then deleted.</li>
</ul>
<p>If you withdraw your consent, we delete your enquiry unless we need to keep it for a legal reason.</p>
<h2 id="your-rights">Your rights</h2>
<p>Under UK data protection law you have the right to:</p>
<ul>
<li>ask for a copy of the personal data we hold about you</li>
<li>ask us to correct data that is wrong or incomplete</li>
<li>ask us to delete your data</li>
<li>ask us to restrict how we use it</li>
<li>object to our use of it where we rely on legitimate interests</li>
<li>ask us to transfer it to you or to another organisation</li>
<li>withdraw your consent at any time, without affecting anything we did before you withdrew it</li>
</ul>
<p>To use any of these rights, email enquiries@wealth-am.co.uk. We will reply within one month. There is no charge.</p>
<h2 id="complaints">Complaints</h2>
<p>If you are unhappy with how we have handled your data, please tell us first so that we can put it right. You also have the right to complain to the Information Commissioner's Office, the UK's data protection regulator, through its website.</p>
<h2 id="changes-to-this-notice">Changes to this notice</h2>
<p>If we change this notice, we will publish the new version here and update the date at the top.</p>
HTML;

$cookies_content = <<<'HTML'
<h2 id="our-cookies">Our cookies</h2>
<p>This website sets no cookies of its own. We do not use analytics, advertising or social media tracking, so there is no cookie banner to accept or decline.</p>
<h2 id="fonts">Fonts</h2>
<p>The site's fonts are loaded from Google Fonts. Google Fonts does not set cookies, but your browser sends your IP address to Google's servers when it requests the font files, as it would for any file on another website.</p>
<h2 id="site-editors">Site editors</h2>
<p>If you log in to edit this site, the content management system sets cookies to keep you signed in and to protect your session. These are strictly necessary for that purpose and are never set for ordinary visitors.</p>
<h2 id="if-this-changes">If this changes</h2>
<p>If we ever add a cookie that is not strictly necessary, we will ask for your consent first and list it on this page.</p>
<h2 id="managing-cookies">Managing cookies</h2>
<p>You can block or delete cookies in your browser settings. Doing so will not affect how this site works for visitors.</p>
HTML;

$accessibility_content = <<<'HTML'
<h2 id="our-commitment">Our commitment</h2>
<p>We want everyone who visits this site to be able to read it, navigate it and contact us, whatever device or assistive technology they use. This statement applies to every page on this demonstration website.</p>
<h2 id="conformance-status">Conformance status</h2>
<p>This website conforms fully to the Web Content Accessibility Guidelines (WCAG) 2.2 at Level AA. Fully conforms means that the content meets every Level A and Level AA success criterion without exception.</p>
<h2 id="what-you-can-expect">What you can expect</h2>
<ul>
<li>Text and interface colours meet contrast ratios of at least 4.5:1, and most body text exceeds 7:1.</li>
<li>Every page can be used with a keyboard alone, with a clear focus outline on every link, button and form field.</li>
<li>Pages have a logical heading structure, landmarks and a link to skip straight to the main content.</li>
<li>Images have text alternatives. Decorative images are hidden from screen readers.</li>
<li>Text can be enlarged to 200% without loss of content, and layouts work on screens from 320 pixels wide.</li>
<li>Animation is kept to a minimum and respects your device's reduced-motion setting.</li>
<li>Form fields have visible labels, and errors are described in words, not by colour alone.</li>
<li>The tabs on the home page work without JavaScript.</li>
</ul>
<h2 id="how-we-tested">How we tested</h2>
<p>We test every page with automated checks and by hand: with a keyboard, with screen readers (VoiceOver on macOS and iOS, NVDA on Windows), at 200% and 400% zoom, and in the latest versions of Chrome, Firefox, Safari and Edge.</p>
<h2 id="known-limitations">Known limitations</h2>
<p>We are not aware of any accessibility barriers on this site. If you find one, please tell us.</p>
<h2 id="reporting-a-problem">Reporting a problem</h2>
<p>If you have difficulty using any part of this site, or need information in a different format, email enquiries@wealth-am.co.uk with "Accessibility" in the subject line, or call +44 (0)20 7946 0123. Tell us the page address and what went wrong. We will reply within five working days.</p>
<h2 id="enforcement">Enforcement</h2>
<p>If you are not happy with our response, you can contact the Equality Advisory and Support Service (EASS).</p>
HTML;

[ $front_id, $c ] = wealth_seed_post( 'page', 'home', [ 'post_title' => 'Home' ], [
	'about_body'            => $about_body,
	'about_heading'         => "A partner-owned firm in St James's",
	'hero_body'             => 'We manage focused equity, credit and multi-asset portfolios for institutional and professional investors.',
	'hero_images'           => array_values( array_filter( [ $img['hero-millennium-bridge.jpg'], $img['hero-tower-bridge.jpg'], $img['hero-escalators.jpg'], $img['hero-underground.jpg'] ] ) ),
	'hero_line_1'           => 'Wealth is an independent',
	'hero_line_2'           => 'investment manager',
	'hero_line_3'           => 'based in London.',
	'opportunities_body'    => $opportunities_body,
	'opportunities_heading' => 'One process, four strategies',
], $img['hero-millennium-bridge.jpg'] );
$created_pages += $c;

[ $contact_id, $c ] = wealth_seed_post( 'page', 'contact', [ 'post_content' => '', 'post_title' => 'Contact' ], [
	'contact_groups' => [
		[ 'email' => 'enquiries@wealth-am.co.uk', 'phone' => '+44 (0)20 7946 0123', 'phone_href' => '+442079460123', 'text' => 'For new mandates, existing investments, due diligence questionnaires and requests for information.', 'title' => 'Investors and consultants' ],
		[ 'email' => 'press@wealth-am.co.uk', 'phone' => '', 'phone_href' => '', 'text' => 'For interviews, comment and requests for data. We aim to reply to journalists the same working day.', 'title' => 'Press' ],
		[ 'email' => 'careers@wealth-am.co.uk', 'phone' => '', 'phone_href' => '', 'text' => 'We recruit rarely and do not advertise every role. If you would like to work in investment research or operations, send a short note and your CV.', 'title' => 'Careers' ],
		[ 'email' => '', 'phone' => '', 'phone_href' => '', 'text' => "Wealth Asset Management LLP, 14 King Street, St James's, London SW1Y 6QU.\n\nOpen Monday to Friday, 8.30am to 6pm. The nearest Underground station is Green Park.", 'title' => 'Our office' ],
	],
	'contact_intro'  => 'We speak directly to pension schemes, insurers, charities, family offices, wealth managers and the consultants who advise them. There is no call centre and no sales team between you and the investment team. Write to us, call us or use the form below, and a named person will reply.',
	'contact_steps'  => [
		[ 'text' => 'A partner decides who is best placed to reply, usually the portfolio manager of the strategy you asked about.', 'title' => 'We read your message the same day.' ],
		[ 'text' => 'If we need more detail about your objectives, we will ask for it then.', 'title' => 'We reply within two working days.' ],
		[ 'text' => "In person in St James's or by video. If none of our strategies fits what you need, we will say so at the first conversation rather than the third.", 'title' => 'We meet if it makes sense.' ],
	],
	'form_intro'     => 'Fields marked optional can be left blank. Everything else is needed so that we can reply.',
	'form_note'      => 'Wealth is a fictional firm. Please do not send real personal or financial information through this form.',
	'subtitle'       => 'Talk to the people who manage the money',
	'thank_you'      => '<p>We have received your message and a member of the team will reply within two working days. If your enquiry is urgent, call us on <a href="tel:+442079460123">+44&nbsp;(0)20&nbsp;7946&nbsp;0123</a>.</p>' . "\n" . '<p><a href="/">Back to the home page</a></p>',
] );
delete_post_thumbnail( $contact_id );
$created_pages += $c;

[ $news_id, $c ] = wealth_seed_post( 'page', 'news', [ 'post_title' => 'Insights' ], [
	'subtitle' => "Notes from our investment team on markets, our strategies and how we use our clients' votes. We write when we have something to say, not to a schedule. Every piece is signed by the person who wrote it, and every view is theirs on the day it was published.",
] );
$created_pages += $c;

[ $legal_id, $c ] = wealth_seed_post( 'page', 'legal', [ 'post_content' => $legal_content, 'post_title' => 'Important information' ], [
	'last_updated'  => '2026-10-01',
	'notice'        => 'This is a demonstration website. Wealth Asset Management is a fictional firm. It is not authorised or regulated by the Financial Conduct Authority or by any other regulator, it does not appear on the Financial Services Register and it does not carry on any regulated activity. The people, strategies, figures and articles on this site are invented. Nothing here is an offer, a recommendation or investment advice.',
	'updated_label' => 'updated',
] );
$created_pages += $c;

[ $privacy_id, $c ] = wealth_seed_post( 'page', 'privacy', [ 'post_content' => $privacy_content, 'post_title' => 'Privacy notice' ], [
	'last_updated'  => '2026-10-01',
	'updated_label' => 'updated',
] );
$created_pages += $c;

[ $cookies_id, $c ] = wealth_seed_post( 'page', 'cookies', [ 'post_content' => $cookies_content, 'post_title' => 'Cookie notice' ], [
	'last_updated'  => '2026-10-01',
	'updated_label' => 'updated',
] );
$created_pages += $c;

[ $accessibility_id, $c ] = wealth_seed_post( 'page', 'accessibility', [ 'post_content' => $accessibility_content, 'post_title' => 'Accessibility statement' ], [
	'last_updated'  => '2026-10-01',
	'updated_label' => 'reviewed',
] );
$created_pages += $c;

echo "pages: {$created_pages} created\n";

$strategies = [
	'uk-equity-income' => [
		'approach'    => [
			[ 'title' => 'Cash before dividends', 'text' => 'We start with free cash flow, not the headline yield. A dividend is only as safe as the cash that pays for it, so we test cover after capital spending, pension contributions and lease payments.' ],
			[ 'title' => 'Balance sheets that can wait', 'text' => 'We favour companies that could survive two difficult years without cutting the dividend or asking shareholders for money. Net debt above three times operating profit needs a very good reason.' ],
			[ 'title' => 'A spread of yields, not just the highest', 'text' => 'We mix established payers with lower-yielding companies whose dividends are growing faster. Positions range from 1.5% to 5% of the portfolio, sized by our conviction and by how easily we could sell.' ],
			[ 'title' => 'Owners, not renters', 'text' => 'We vote every share and meet the chair as well as the finance director. When we disagree with a board on pay, capital allocation or succession, we tell them, and we tell you.' ],
		],
		'cta_body'    => "Tell us what your income needs to do, whether that is meeting pension payments, funding grants or supporting a client's drawdown, and we will show you how this strategy has behaved in years like the ones you are planning for.",
		'cta_heading' => 'Discuss an income mandate',
		'documents'   => [
			[ 'label' => 'Factsheet, September 2026' ],
			[ 'label' => 'Key information summary' ],
			[ 'label' => 'Quarterly letter, second quarter 2026' ],
		],
		'facts'       => [
			[ 'label' => 'Asset class', 'value' => 'UK equities' ],
			[ 'label' => 'Benchmark', 'value' => 'FTSE All-Share Index (total return)' ],
			[ 'label' => 'Launch', 'value' => '3 March 2014' ],
			[ 'label' => 'Base currency', 'value' => 'GBP' ],
			[ 'label' => 'Typical holdings', 'value' => '35 to 45 companies' ],
			[ 'label' => 'Liquidity', 'value' => 'Daily dealing in the pooled fund; segregated mandates by agreement' ],
		],
		'faqs'        => [
			[ 'question' => 'How often is income paid?', 'answer' => 'Quarterly, at the end of February, May, August and November. Investors in the pooled fund can take income or have it reinvested.' ],
			[ 'question' => 'What happens when a company cuts its dividend?', 'answer' => 'We do not sell automatically. We ask whether the cut was prudent or forced. A board that cuts to protect the balance sheet can be a better owner than one that borrows to keep paying. If the cut reveals a problem we missed, we sell.' ],
			[ 'question' => 'What is the minimum for a segregated mandate?', 'answer' => 'Usually £50 million. Below that, the pooled fund gives the same portfolio at lower cost.' ],
		],
		'intro'       => <<<'HTML'
<p>The UK market pays one of the higher dividend yields among developed markets, but a high yield on its own tells you little. Some of the most generous payers in the index are businesses in slow decline, handing back cash they will not have in five years' time.</p>
<p>We look for the opposite: companies that earn more cash than they need, spend it sensibly and can raise their dividend through a downturn. They are often unglamorous. A specialist insurer, a building materials distributor, a water utility with a clear regulatory settlement, a software business that sells to accountants.</p>
<p>The strategy launched in March 2014 and is run by Eleanor Whitcombe and Daniel Okafor. It is available as a segregated mandate or through a pooled fund for eligible investors.</p>
HTML,
		'managers'    => [ 'eleanor-whitcombe', 'daniel-okafor' ],
		'objective'   => 'To provide an income above that of the FTSE All-Share Index, with the potential for capital growth, over rolling five-year periods.',
		'risks'       => <<<'HTML'
<p>This is a concentrated portfolio of UK shares. Its value will rise and fall with the UK stock market, and because it holds fewer companies than the index it can behave quite differently from it, for better or worse, over long periods. You may get back less than you invest.</p>
<p>Dividends are not fixed. Companies can reduce or suspend them without notice, as many did in 2020. If several holdings cut at once, the income from the strategy will fall, and it may take years to recover.</p>
<p>Some holdings are medium-sized companies whose shares trade less often than those of the largest firms. In a falling market they can be harder to sell at a fair price. Many UK companies also earn much of their profit overseas, so changes in exchange rates affect their value.</p>
HTML,
		'summary'     => 'A concentrated portfolio of 35 to 45 UK-listed companies, chosen for the strength of their cash flows and the discipline of their boards. We aim for an income above that of the FTSE All-Share Index, and for that income to grow over time. We do not reach for yield in businesses that cannot pay it.',
		'tagline'     => 'Dependable income from resilient British businesses',
		'tint'        => 'blue',
		'title'       => 'UK Equity Income Strategy',
	],
	'european-quality-growth' => [
		'approach'    => [
			[ 'title' => 'Returns on capital first', 'text' => 'We want companies that have earned a return on invested capital above 15% through a full cycle, not just in a good year. That usually points to pricing power, a strong brand or high switching costs.' ],
			[ 'title' => 'A long runway', 'text' => 'High returns matter only if a company can reinvest. We look for markets that are growing, fragmented or still moving from older technology, so profits can go back into the business at similar rates.' ],
			[ 'title' => 'Price discipline', 'text' => "Quality is not a reason to pay any price. We model each company's cash flows over five years and buy only where we see a reasonable return from today's share price. We trim when that return falls away." ],
			[ 'title' => 'Patience', 'text' => 'Turnover is typically 15% to 20% a year, which implies an average holding period of five years or more. We would rather know 35 companies well than 100 a little.' ],
		],
		'cta_body'    => 'Daniel Okafor meets investors and their consultants in London and on the continent. If you are reviewing your European equity allocation, we will take you through the portfolio company by company.',
		'cta_heading' => 'Talk to the manager',
		'documents'   => [
			[ 'label' => 'Factsheet, September 2026' ],
			[ 'label' => 'Key information summary' ],
			[ 'label' => 'Quarterly letter, second quarter 2026' ],
		],
		'facts'       => [
			[ 'label' => 'Asset class', 'value' => 'European equities, excluding the UK' ],
			[ 'label' => 'Benchmark', 'value' => 'MSCI Europe ex UK Index (net total return)' ],
			[ 'label' => 'Launch', 'value' => '12 June 2017' ],
			[ 'label' => 'Base currency', 'value' => 'EUR, with a GBP share class' ],
			[ 'label' => 'Typical holdings', 'value' => '30 to 40 companies' ],
			[ 'label' => 'Liquidity', 'value' => 'Daily dealing' ],
		],
		'faqs'        => [
			[ 'question' => 'Why leave out the UK?', 'answer' => 'Most of our clients already own UK shares, often through our income strategy. Keeping the two apart lets them decide their own balance between the UK and the continent.' ],
			[ 'question' => 'Do you hedge the currency?', 'answer' => 'Not in the pooled fund. Sterling investors carry the euro exposure. Segregated mandates can be hedged back to sterling if a client prefers.' ],
			[ 'question' => 'How do you define a quality company?', 'answer' => 'One that earns high returns on its capital, turns most of its profit into cash, has a balance sheet that does not need the goodwill of lenders and is run by people who think like owners.' ],
		],
		'intro'       => <<<'HTML'
<p>Europe is home to hundreds of companies that lead the world in something narrow. A maker of precision valves for semiconductor plants. A supplier of laboratory consumables. A Nordic business that builds the software behind half the region's ports. They rarely make headlines, and they are often under-owned by investors who track an index dominated by banks, energy companies and a few household names.</p>
<p>We look for businesses that earn well above their cost of capital and can keep reinvesting at those rates for a decade or more. Then we try to buy them at prices that leave room for disappointment.</p>
<p>The strategy launched in June 2017 and is run by Daniel Okafor. It is priced in euros, with a sterling share class for UK investors.</p>
HTML,
		'managers'    => [ 'daniel-okafor' ],
		'objective'   => 'To achieve capital growth above that of the MSCI Europe ex UK Index, measured in euros, over rolling five-year periods.',
		'risks'       => <<<'HTML'
<p>The strategy invests only in European shares and holds a small number of them. Its value will move with European stock markets and can differ sharply from the index. Capital is at risk and you may get back less than you invest.</p>
<p>Quality growth is a style, and styles go in and out of favour. When markets rally hard in cheaper, more cyclical companies, or when interest rates rise quickly, this strategy can lag for a year or more. That happened in 2022, when the valuations of many of our holdings fell even though their profits did not.</p>
<p>Several holdings are mid-sized companies whose shares trade less often, so they can be harder to sell quickly. The share classes are not hedged: if you invest in sterling, a stronger pound will reduce the value of your holding even if the shares themselves have not moved.</p>
HTML,
		'summary'     => 'A portfolio of 30 to 40 continental European companies with high returns on capital, strong positions in their markets and room to reinvest. Many are mid-sized, often family-influenced businesses in industrial and healthcare niches that few investors follow closely. We hold them for years and let compounding do the work.',
		'tagline'     => "Compounding quality across Europe's quiet leaders",
		'tint'        => 'lavender',
		'title'       => 'European Quality Growth Strategy',
	],
	'short-dated-sterling-credit' => [
		'approach'    => [
			[ 'title' => 'Short by design', 'text' => "We buy bonds that mature within five years and keep the portfolio's duration between one and three years. We do not stretch for yield by moving further out." ],
			[ 'title' => 'Our own credit research', 'text' => "Credit ratings are a starting point, not a decision. We analyse each issuer's cash flows, refinancing needs and covenants ourselves, and we avoid issuers whose bonds we would not be content to hold to maturity." ],
			[ 'title' => 'Diversified, not diluted', 'text' => 'No single corporate issuer may exceed 3% of the portfolio. Up to 10% may be held in bonds rated BB, where our research gives us confidence, and the rest is rated BBB or above.' ],
			[ 'title' => 'A buffer of cash and gilts', 'text' => 'We keep 5% to 10% in gilts and treasury bills so we can meet redemptions without selling corporate bonds into a weak market.' ],
		],
		'cta_body'    => 'If you hold reserves or near-term liabilities in cash and want to know what a short-dated credit portfolio would have done with them, Sophie Lindqvist and the fixed income team will model it for you.',
		'cta_heading' => 'Find a home for your reserves',
		'documents'   => [
			[ 'label' => 'Factsheet, September 2026' ],
			[ 'label' => 'Key information summary' ],
			[ 'label' => 'Quarterly letter, second quarter 2026' ],
		],
		'facts'       => [
			[ 'label' => 'Asset class', 'value' => 'Sterling corporate bonds' ],
			[ 'label' => 'Benchmark', 'value' => 'ICE BofA 1–5 Year Sterling Corporate Index' ],
			[ 'label' => 'Launch', 'value' => '3 October 2016' ],
			[ 'label' => 'Base currency', 'value' => 'GBP' ],
			[ 'label' => 'Typical holdings', 'value' => '60 to 90 issuers' ],
			[ 'label' => 'Liquidity', 'value' => 'Daily dealing' ],
		],
		'faqs'        => [
			[ 'question' => 'Is this an alternative to cash?', 'answer' => 'No. It aims for a higher return than cash over three years, but its value can fall, and it did in 2022. It suits money that can sit for at least 18 months.' ],
			[ 'question' => 'Do you buy high-yield bonds?', 'answer' => 'A little. Up to 10% may be in bonds rated BB, the highest grade below investment grade, where our own research supports it. We do not buy anything rated below BB.' ],
			[ 'question' => 'How is income paid?', 'answer' => 'Monthly, or reinvested. Many charity and insurance clients use the monthly distribution to match their own outgoings.' ],
		],
		'intro'       => <<<'HTML'
<p>Short-dated credit is an unfashionable asset class. It rarely makes the news, and in a good year for markets it will trail almost everything else. That is the point. Its job is to protect capital and earn a steady yield while longer-dated bonds and equities do the more volatile work.</p>
<p>Bonds that mature within five years are less sensitive to interest rates, and they keep returning cash to the portfolio. That cash can be reinvested at current yields, so the portfolio adjusts to a changing rate environment within a year or two rather than a decade.</p>
<p>The strategy launched in October 2016 and is run by Sophie Lindqvist. Insurers, charities and pension schemes use it as a home for reserves, for cash flows they will need in the next few years, and as a lower-risk part of a wider bond allocation.</p>
HTML,
		'managers'    => [ 'sophie-lindqvist' ],
		'objective'   => 'To provide a return above that of the ICE BofA 1–5 Year Sterling Corporate Index over rolling three-year periods, while limiting the fall in capital value when interest rates or credit spreads rise.',
		'risks'       => <<<'HTML'
<p>This is not a cash deposit and its value is not protected. The companies we lend to can fail to pay interest or repay their bonds. We spread that risk across many issuers, but a default would reduce the value of the portfolio. Capital is at risk.</p>
<p>When interest rates rise, the price of existing bonds falls. Because the bonds are short-dated, the effect is smaller than in a longer bond fund but still real: with a duration of two years, a one percentage point rise in yields would reduce the value of the portfolio by around 2%, before income.</p>
<p>In a disorderly market, as in March 2020 or during the gilt market stress of autumn 2022, even good-quality short bonds can become hard to sell at a fair price for a period. Our buffer of gilts and treasury bills is there for those moments, but it cannot remove the risk.</p>
HTML,
		'summary'     => 'A portfolio of sterling bonds from 60 to 90 companies and institutions, mostly investment grade, with maturities of up to five years. It is built for investors who want more than cash or short-dated gilts can offer, but who cannot accept large swings in capital value. Duration is typically between 1.5 and 2.5 years.',
		'tagline'     => 'Capital preservation with a sensible yield',
		'tint'        => 'paper',
		'title'       => 'Short-Dated Sterling Credit Strategy',
	],
	'global-multi-asset-income' => [
		'approach'    => [
			[ 'title' => 'Income from many sources', 'text' => 'Dividends from global shares, coupons from gilts and corporate bonds, and distributions from listed infrastructure and property. No single source is allowed to dominate the income.' ],
			[ 'title' => 'Allocation within set ranges', 'text' => 'Equities 30% to 60%, bonds 25% to 55%, infrastructure and property 5% to 20%, cash up to 10%. The committee moves within those ranges when valuations change, not in response to headlines.' ],
			[ 'title' => 'Direct holdings, not funds of funds', 'text' => "We buy shares and bonds ourselves rather than other managers' funds. That keeps costs down and means we can show you every holding and every vote." ],
			[ 'title' => 'Sterling at the centre', 'text' => 'At least 80% of overseas currency exposure is hedged back to sterling, so the income you receive is not swamped by exchange-rate moves.' ],
		],
		'cta_body'    => 'Wealth managers and charities use this strategy to fund regular payments without building and rebalancing a portfolio of funds. Tell us the income you need to fund and how often, and Tomás Herrera will explain how the strategy could fit.',
		'cta_heading' => 'Plan an income from one portfolio',
		'documents'   => [
			[ 'label' => 'Factsheet, September 2026' ],
			[ 'label' => 'Key information summary' ],
			[ 'label' => 'Quarterly letter, second quarter 2026' ],
		],
		'facts'       => [
			[ 'label' => 'Asset class', 'value' => 'Multi-asset: global equities, bonds, listed infrastructure and property' ],
			[ 'label' => 'Benchmark', 'value' => 'No formal benchmark. Comparator: UK Consumer Prices Index plus 3% a year' ],
			[ 'label' => 'Launch', 'value' => '1 February 2021' ],
			[ 'label' => 'Base currency', 'value' => 'GBP' ],
			[ 'label' => 'Typical holdings', 'value' => '120 to 180 securities' ],
			[ 'label' => 'Liquidity', 'value' => 'Weekly dealing, every Wednesday' ],
		],
		'faqs'        => [
			[ 'question' => 'Why does the fund deal weekly rather than daily?', 'answer' => 'Some of our infrastructure and property holdings trade less often. Weekly dealing lets us meet subscriptions and redemptions without forcing trades in those shares, which protects investors who stay in the fund.' ],
			[ 'question' => 'Is the income the same every quarter?', 'answer' => 'No. It reflects what the holdings pay. We publish the income paid in each of the last four quarters on the factsheet so you can see how much it varies.' ],
			[ 'question' => 'How does this differ from holding your other strategies side by side?', 'answer' => 'It draws on the same research, but it adds global shares, gilts and real assets that our other strategies do not hold, and it changes the balance between them as conditions change.' ],
		],
		'intro'       => <<<'HTML'
<p>An income that relies on one asset class is exposed to that asset class's bad years. Equity dividends were cut sharply in 2020. Bond yields were close to zero for most of the decade before 2022. A portfolio that draws on several sources can keep paying while one of them struggles.</p>
<p>We combine our own equity and credit research with a clear view on how much of each to hold. An asset allocation committee, chaired by Eleanor Whitcombe, sets the ranges each quarter. Tomás Herrera runs the portfolio day to day, with Sophie Lindqvist responsible for the bond holdings.</p>
<p>The strategy launched in February 2021. It is available to eligible investors through a pooled fund that deals weekly, and as a segregated mandate.</p>
HTML,
		'managers'    => [ 'tomas-herrera', 'sophie-lindqvist' ],
		'objective'   => 'To provide an income, with some capital growth, while aiming for lower volatility than global equities, over rolling five-year periods.',
		'risks'       => <<<'HTML'
<p>The strategy carries the risks of every asset class it holds: shares can fall, bond issuers can default, and the prices of bonds and property shares fall when interest rates rise. Capital is at risk and you may get back less than you invest.</p>
<p>Holding several asset classes is no assurance that they will behave differently. In 2022 shares and bonds fell together, and a balanced portfolio offered less protection than usual. That can happen again.</p>
<p>The income is not fixed. It depends on the dividends and coupons the underlying holdings pay, and it will vary from quarter to quarter. We use currency forwards to hedge overseas exposure. These contracts protect against most exchange-rate moves but not all, and they carry a small risk that the bank on the other side fails to pay.</p>
HTML,
		'summary'     => 'A single portfolio that combines global dividend-paying shares, government and corporate bonds, and listed infrastructure and property. It is built for investors who need an income from one place and would rather not assemble the mix themselves. We hold securities directly, not other funds, so every cost and every holding is visible.',
		'tagline'     => 'A balanced income across asset classes and regions',
		'tint'        => 'tan',
		'title'       => 'Global Multi-Asset Income Strategy',
	],
];

$strategy_ids       = [];
$created_strategies = 0;
$order              = 0;
foreach ( $strategies as $slug => $strategy ) {
	$order++;
	[ $id, $c ] = wealth_seed_post( 'strategy', $slug, [ 'menu_order' => $order, 'post_title' => $strategy['title'] ], [
		'approach'      => $strategy['approach'],
		'cta_body'      => $strategy['cta_body'],
		'cta_heading'   => $strategy['cta_heading'],
		'documents'     => $strategy['documents'],
		'facts'         => $strategy['facts'],
		'faqs'          => $strategy['faqs'],
		'feature_image' => $img[ 'strategy-' . $slug . '.jpg' ],
		'intro'         => $strategy['intro'],
		'objective'     => $strategy['objective'],
		'risks'         => $strategy['risks'],
		'summary'       => $strategy['summary'],
		'tagline'       => $strategy['tagline'],
		'tint'          => $strategy['tint'],
	] );
	$strategy_ids[ $slug ] = $id;
	$created_strategies   += $c;
}
echo "strategies: {$created_strategies} created, " . count( $strategy_ids ) . " total\n";

$team = [
	'eleanor-whitcombe' => [
		'content'    => '<p>Eleanor began her career in 1999 as a UK equity analyst in the investment arm of a large British life insurer, covering utilities and telecoms and later the banks. In 2006 she moved to a long-established City fund house, where she managed UK income portfolios for pension schemes and charities for seven years and sat on its stewardship committee.</p><p>She left in 2013 to found Wealth with James Hartley. Her view then, and now, is that a small firm with a fixed capacity serves clients better than a large one with a sales target. She read Modern History at Durham University and is a trustee of an almshouse charity in south London.</p><p>Outside work she grows dahlias on an allotment in Camberwell and is walking the Thames Path from source to sea, one weekend at a time.</p>',
		'group'      => 'investment',
		'lead'       => 'Eleanor Whitcombe co-founded Wealth in 2013 and has led its investment team ever since. She has managed UK equity income portfolios for more than 20 years and still runs the UK Equity Income Strategy herself, with Daniel Okafor. As Chief Investment Officer she chairs the asset allocation and stewardship committees, and every position the firm takes passes across her desk. Clients know her for short answers and long holding periods.',
		'name'       => 'Eleanor Whitcombe',
		'role'       => 'Chief Investment Officer',
	],
	'daniel-okafor' => [
		'content'    => '<p>Daniel grew up in Leeds and read Economics at the University of Manchester. He trained as a chartered accountant with a large audit firm, working mostly on engineering and manufacturing clients in the north of England. The experience taught him how a factory makes money and how a set of accounts can hide that it does not.</p><p>In 2008 he moved into investment as a European industrials analyst in the London office of a large continental asset manager. Four years later he went to Frankfurt to co-manage a European small and mid-cap fund for the investment arm of a German private bank. He joined Wealth in 2016 to build the European strategy and became Head of Equities in 2021. He speaks German and passable Dutch.</p><p>He holds a season ticket at Elland Road and rides one Alpine pass every summer, usually a little more slowly than the year before.</p>',
		'group'      => 'investment',
		'lead'       => "Daniel Okafor leads the firm's equity research and manages the European Quality Growth Strategy, which he launched in 2017. He also co-manages the UK Equity Income Strategy with Eleanor Whitcombe. Daniel trained as an accountant, and it shows: he reads the notes to the accounts before the chair's statement, and he will not buy a company until he can say where its cash goes. He spends a week in every month visiting companies on the continent.",
		'name'       => 'Daniel Okafor',
		'role'       => 'Head of Equities',
	],
	'sophie-lindqvist' => [
		'content'    => '<p>Sophie was born in Gothenburg and studied at the Stockholm School of Economics before taking an MSc in Finance at the London School of Economics. She joined the London credit research team of a Scandinavian bank in the summer of 2008, weeks before the financial crisis reached its worst. She spent her first year learning what happens to bonds when nobody wants to buy them.</p><p>From 2011 she managed short-dated sterling credit for the in-house investment team of a UK annuity insurer, where every portfolio had to meet a schedule of pension payments. That discipline, matching what you own to what you owe, shapes how she runs money today.</p><p>She swims in the Hampstead ponds all year round and sings alto in a chamber choir in Islington.</p>',
		'group'      => 'investment',
		'lead'       => 'Sophie Lindqvist manages the Short-Dated Sterling Credit Strategy, which she joined Wealth to build in 2016, and the bond holdings in the Global Multi-Asset Income Strategy. She has spent 18 years looking at companies the way a lender does: what they owe, when it falls due and what happens if things go wrong. She and her team of three analysts follow around 200 sterling issuers. She is a CFA charterholder.',
		'name'       => 'Sophie Lindqvist',
		'role'       => 'CFA, Portfolio Manager, Fixed Income',
	],
	'james-hartley' => [
		'content'    => '<p>James began as an economist in the civil service, forecasting public borrowing. In 1998 he joined a City investment house as a gilt and interest-rate strategist, and he spent the next decade writing about central banks for its fixed income clients. He then moved across to run its UK institutional client team and later its whole UK business.</p><p>He founded Wealth with Eleanor Whitcombe because he had watched too many good investment teams grow until they could no longer do what had made them good. He read Economics at the University of Cambridge. He is a governor of a secondary school in Lambeth and sits on the investment committee of a medical research charity.</p><p>At home in East Sussex he keeps bees, with mixed results.</p>',
		'group'      => 'executive',
		'lead'       => "James Hartley co-founded Wealth in 2013 and has been Chief Executive since. He is responsible for the firm's direction, its partnership and its relationships with its largest clients. James spent the first half of his career in sterling bond markets and the second running client businesses. As a result he asks of every investment decision how it will look to the person whose money it is. He chairs the management committee.",
		'name'       => 'James Hartley',
		'role'       => 'Chief Executive',
	],
	'hannah-mercer' => [
		'content'    => "<p>Hannah read Mathematics at the University of Bristol and qualified as a chartered accountant while working in fund accounting at a global custodian. She spent five years reconciling other people's portfolios and learned that most operational failures start with a spreadsheet nobody owns.</p><p>In 2007 she joined a mid-sized London fund manager and became its head of operations. There she led the move to a new fund administrator, rebuilt client reporting from scratch and ran the firm's response to two changes of regulation. At Wealth she has kept the operating model deliberately simple: one administrator, one depositary, one set of numbers that every team uses.</p><p>She runs fell races in the Lake District most summers and is treasurer of a youth orchestra in Richmond.</p>",
		'group'      => 'executive',
		'lead'       => "Hannah Mercer runs everything at Wealth that is not investment: operations, risk, compliance, technology and client reporting. She joined in 2015 from a London fund manager, where she was head of operations, and became Chief Operating Officer and a partner in 2018. The plain quarterly reports our clients receive are her design. She chairs the risk committee and is the firm's main contact with its depositary, auditors and fund administrator.",
		'name'       => 'Hannah Mercer',
		'role'       => 'Chief Operating Officer',
	],
	'tomas-herrera' => [
		'content'    => "<p>Tomás was born in Seville and grew up in Madrid. He studied Economics at Universidad Carlos III de Madrid and took an MSc in Financial Mathematics at the University of Warwick. He started as a quantitative analyst in the asset management arm of a Spanish insurer, modelling how long policyholders would live and what that meant for the bonds the company held.</p><p>In 2011 he moved to London to join the fiduciary management team of a pensions consultancy, running multi-asset portfolios for defined benefit schemes, and later led its income portfolios. He joined Wealth in 2020 to design the multi-asset strategy from first principles, using the firm's own equity and credit research rather than other managers' funds.</p><p>He plays chess for a club in Hammersmith and is visiting, without much hurry, every Romanesque church in Castile.</p>",
		'group'      => 'investment',
		'lead'       => 'Tomás Herrera manages the Global Multi-Asset Income Strategy, which he launched at Wealth in 2021. He decides how the portfolio is divided between shares, bonds and real assets, within the ranges set by the asset allocation committee, and he picks the global equity and infrastructure holdings himself. Before Wealth he spent a decade building income portfolios for pension schemes. He is a CFA charterholder and thinks in scenarios rather than forecasts.',
		'name'       => 'Tomás Herrera',
		'role'       => 'CFA, Portfolio Manager, Multi-Asset',
	],
];

$team_ids     = [];
$created_team = 0;
$order        = 0;
foreach ( $team as $slug => $member ) {
	$order++;
	[ $id, $c ] = wealth_seed_post( 'team_member', $slug, [ 'menu_order' => $order, 'post_content' => $member['content'], 'post_title' => $member['name'] ], [
		'email'      => str_replace( '-', '.', $slug ) . '@wealth-am.co.uk',
		'group'      => $member['group'],
		'lead'       => $member['lead'],
		'linkedin'   => 'https://www.linkedin.com/in/' . $slug,
		'role'       => $member['role'],
		'x'          => 'https://x.com/' . str_replace( '-', '', $slug ),
	], $img[ 'team-' . $slug . '.jpg' ] );
	delete_post_meta( $id, 'strategies' );
	$team_ids[ $slug ] = $id;
	$created_team     += $c;
}
echo "team members: {$created_team} created, " . count( $team_ids ) . " total\n";

foreach ( $strategies as $slug => $strategy ) {
	update_post_meta( $strategy_ids[ $slug ], 'managers', array_map( fn ( $m ) => $team_ids[ $m ], $strategy['managers'] ) );
}
echo "managers: linked to " . count( $strategy_ids ) . " strategies\n";

$categories = [
	'markets'     => 'Markets',
	'stewardship' => 'Stewardship',
	'strategy'    => 'Strategy',
];
foreach ( $categories as $slug => $name ) {
	if ( ! term_exists( $slug, 'category' ) ) {
		wp_insert_term( $name, 'category', [ 'slug' => $slug ] );
	}
}
echo "categories: ready\n";

$article_income = <<<'HTML'
<p>For most of the decade before 2022, the case for equity income made itself. Bank Rate sat close to zero. 10-year gilts yielded less than 2%, and often less than 1%. If you needed an income from your capital, a portfolio of dividend-paying shares was one of the few places to find one.</p>
<p>That world has gone. Cash deposits and short-dated gilts now pay more than 3%, and you can lock in a yield above 4% on high-quality corporate bonds without taking any equity risk at all. The question we are asked most often by trustees and their advisers is a fair one: why bother with equity income now?</p>
<p>Our answer has three parts.</p>
<h2>Cash pays today. Dividends can grow.</h2>
<p>A deposit or a short bond pays a fixed amount. When it matures, you reinvest at whatever rate the market offers. If rates fall, your income falls with them.</p>
<p>A dividend works differently. It is a share of the profits of a business, and if the business grows, the dividend can grow too. Take two portfolios of £10 million. One sits in cash at 4%. The other is invested in UK shares yielding 3.8%. In the first year the cash pays slightly more. But if the dividends from the share portfolio grow by 5% a year, the share portfolio's income overtakes the cash in the third year, and by the tenth year it pays around £590,000 against the cash's £400,000, assuming the deposit rate holds.</p>
<p>That growth is not assured. UK dividends fell by more than a third in 2020 and took three years to recover. That is why the second part of our answer matters more than the first.</p>
<h2>Higher rates have changed which dividends are safe</h2>
<p>When money was nearly free, a company could borrow to keep its dividend going and pay very little for the privilege. Many did. Some listed landlords, utilities and consumer businesses paid out more than they earned in cash for years, and covered the gap with debt.</p>
<p>That debt is now coming due. A company that borrowed at 2% in 2020 and must refinance in 2027 may pay 6% or more on the same money. The extra interest comes straight out of the cash that would otherwise fund the dividend.</p>
<p>Over the past 18 months we have gone through every holding in the UK Equity Income Strategy and mapped its debt maturities against what refinancing would cost at today's rates. It was slow work and it changed the portfolio.</p>
<p>We sold our position in a listed landlord whose bonds maturing in 2027 would cost almost three times their current coupon to replace. Its dividend looked safe on paper. It did not look safe once we redid the interest bill. We also reduced a consumer brands business that had funded buybacks with borrowing.</p>
<p>On the other side, we added to two general insurers. Insurers hold large portfolios of short bonds to meet future claims, and higher rates have lifted the income on those portfolios sharply. We also bought a water company after its price settlement for 2025 to 2030 gave it a clearer view of what it could earn and spend.</p>
<blockquote class="article__pullquote"><p>A yield is only a board's statement of intent. Our work is judging which boards can afford it.</p></blockquote>
<h2>What income does inside a portfolio</h2>
<p>The third part of our answer is about discipline rather than arithmetic.</p>
<p>A company that commits to a dividend has to find the cash every six months. That limits how much it can spend on large acquisitions, vanity projects and empire-building. It forces management to choose. Over long periods we think that pressure leads to better use of capital, and the record of UK shares supports it: by most measures, reinvested dividends account for more than half of the total return from the UK market over the past 30 years.</p>
<p>Income also changes how investors behave. Trustees who receive a steady cash return are less likely to sell at the bottom of a market. Charities can plan grants. Pension schemes can meet payments without selling shares when prices are low. A portfolio that produces cash gives its owners room to be patient.</p>
<h2>Where we are finding it now</h2>
<p>The FTSE All-Share Index yields around 3.5%. The UK Equity Income Strategy yields around 4.4%, and we have built that from companies whose dividends were covered by free cash flow at least 1.5 times last year.</p>
<p>The yield comes from a wider range of businesses than many people expect. Alongside the insurers and the water company, the portfolio holds a building materials distributor, two medium-sized engineering firms that sell into the defence and energy supply chains, a software business that serves accountancy practices and a food producer that has raised its dividend through every year of high inflation.</p>
<p>What we are avoiding is just as telling. We do not own the highest-yielding shares in the index for their own sake. In our experience a yield above 8% is more often a warning than an opportunity: the market is usually telling you the payment will be cut. We would rather own a company yielding 3% that can raise its dividend by 8% a year than one yielding 9% that cannot hold it.</p>
<h2>The case, restated</h2>
<p>Higher rates have made cash and bonds a real alternative, and we are glad of it. Our own clients hold both, often with us. But a deposit cannot grow its income, and a bond cannot raise its coupon. A well-chosen portfolio of UK businesses can do both, provided you are careful about which dividends you trust.</p>
<p>That care is the job. It is also why we run the strategy with 35 to 45 holdings rather than a few hundred. We would rather know each company well enough to tell you why we expect its dividend to be paid than own enough of them to stop asking.</p>
<p class="article__disclaimer">This article reflects the author's views on the date of publication. It is not investment advice or a recommendation to buy or sell any security. Capital is at risk and past performance is not a guide to future returns.</p>
HTML;

$article_credit = <<<'HTML'
<p>For most of the last decade, short-dated sterling credit was a hard sell. A portfolio of high-quality company bonds maturing within five years yielded less than 2%, and for long stretches less than 1%. Once you took off costs and inflation, investors were paying for the privilege of lending.</p>
<p>That has changed. Today the Short-Dated Sterling Credit Strategy has a yield to maturity of around 4.8% and a duration of 2.1 years. For the first time in many years, investors are paid a real return for lending to good companies for short periods.</p>
<p>This note sets out what that yield is made of, why we keep the maturities short, and what could go wrong.</p>
<h2>What you are paid</h2>
<p>The yield on a corporate bond has two parts. The first is the yield on a gilt of the same maturity, which reflects where the market thinks Bank Rate will be over the life of the bond. Two-year gilts currently yield around 3.8%. The second part is the credit spread: the extra yield a company pays because it is a riskier borrower than the government. Across our portfolio that spread averages around one percentage point.</p>
<p>Most of the yield, in other words, comes from the gilt market rather than from credit risk. That matters. It means investors are not being paid to take on a lot of default risk. They are being paid mainly because interest rates are higher than they were.</p>
<h2>Why short, not long</h2>
<p>Longer-dated corporate bonds yield a little more, around 5.3% for a broad sterling index with a duration of about six years. But duration is the measure of how much a bond's price falls when yields rise, and the difference in risk is large.</p>
<p>A simple way to see it is to ask how far yields would need to rise over a year before the price loss wiped out the income. For our portfolio, with a yield of 4.8% and a duration of 2.1 years, yields would need to rise by about 2.3 percentage points. For the longer index, with a yield of 5.3% and a duration of six years, a rise of less than one percentage point would be enough.</p>
<p>Short bonds also return cash quickly. Around a fifth of the portfolio matures each year. That cash is reinvested at whatever yields are available, so if rates rise, the portfolio's income rises with them within a year or two. If rates fall, the bonds we already own continue to pay the higher coupons until they mature.</p>
<blockquote class="article__pullquote"><p>Short-dated credit will not make anyone rich. Its job is to make sure that when you need the money, it is there.</p></blockquote>
<h2>Spreads are tight. That is a reason to be selective.</h2>
<p>Credit spreads are narrow by historical standards. Investors are not being paid much extra for lending to companies rather than to the government, and when that happens it is usually a sign that markets are relaxed about risk. They are not always right to be.</p>
<p>We have responded in three ways over the past year.</p>
<p>First, we have reduced our holdings of bonds rated BB, the highest grade below investment grade, from 9% of the portfolio to 4%. The extra yield on offer no longer pays for the risk.</p>
<p>Second, we have added to borrowers whose cash flows do not depend on the economic cycle: regulated utilities, housing associations with strong rent collection, and supranational and agency issuers such as development banks. These yield less than the average corporate bond, but they give us room to be patient.</p>
<p>Third, we have shortened. More of the portfolio now matures within two years than at any time since 2021. If spreads widen, we will have cash coming back to buy with.</p>
<h2>What could go wrong</h2>
<p>The main risk in any credit portfolio is that a borrower fails to pay. We spread that risk across around 80 issuers, and no corporate issuer is more than 3% of the portfolio. We read the bond documents, model each company's refinancing needs and avoid businesses whose debts we would not want to hold to maturity. Even so, defaults happen, and one would reduce the value of the portfolio.</p>
<p>The second risk is liquidity. In March 2020, and again during the gilt market stress of autumn 2022, even high-quality short bonds became hard to sell at sensible prices for a few weeks. Prices fell, including in this strategy. We keep between 5% and 10% of the portfolio in gilts and treasury bills so that we can meet redemptions in those periods without selling corporate bonds at the worst moment. In 2022 that buffer meant we sold no corporate bonds at all, and as the bonds moved closer to maturity their prices recovered. Past performance is not a guide to the future, and the buffer reduces the risk rather than removing it.</p>
<p>The third risk is that rates fall faster than expected. If Bank Rate were cut sharply, the yield on new bonds would fall, and over time so would the portfolio's income. That is the trade-off of keeping maturities short: less risk to capital, less certainty about future income.</p>
<h2>Who uses it, and how</h2>
<p>Our clients use the strategy in different ways. Charities hold reserves in it that they may need within three to five years. Insurers use it to back claims they expect to pay in the near term. Pension schemes use it to cover benefit payments that fall due before longer-dated assets mature. Wealth managers use it as the lower-risk part of a cautious portfolio.</p>
<p>What they share is a need for capital to be there when they expect to need it, with a yield that keeps pace with inflation in the meantime. Short-dated credit is not the only way to meet that need, and it is not a substitute for cash that must be available tomorrow. But at today's yields it does the job better than it has for a long time.</p>
<p class="article__disclaimer">This article reflects the author's views on the date of publication. It is not investment advice or a recommendation to buy or sell any security. Capital is at risk and past performance is not a guide to future returns.</p>
HTML;

$article_europe = <<<'HTML'
<p>Ask a European company's finance director how long their shareholders stay, and the answer is usually measured in months. Trading is cheap, information is instant and many funds are judged every quarter. The result is a market that reacts sharply to news about the next six months and pays little attention to the next 10 years.</p>
<p>We think that is a durable advantage for an investor in Europe. If most money looks at the near term, patient capital faces less competition further out.</p>
<p>In practice, that means holding companies for a long time. Turnover in the European Quality Growth Strategy was 17% over the past year, which implies an average holding period of close to six years. Several companies have been in the portfolio since it launched in 2017.</p>
<p>One of them is a German maker of dosing pumps used in water treatment and chemical plants. When we bought it, it earned a return on capital of around 20% and had a long record of reinvesting in new products and markets. In 2022 its shares fell by more than 40% as interest rates rose and investors sold anything with a high valuation. Over the same period its revenue grew by 11% and its order book reached a record.</p>
<p>We did not sell. We added to the position twice in the autumn of 2022, because the business was doing what we had bought it to do and the price had moved in our favour. The shares have since recovered.</p>
<p>Patience has costs, and it would be dishonest to pretend otherwise. Quality growth companies are rarely cheap, and in years when markets favour banks, energy and other cyclical businesses, the strategy can trail the index for a long time. Holding through a 40% fall is uncomfortable for us and for our clients. We send a letter every quarter that explains, company by company, why we still hold what we hold.</p>
<p>Patience is also not inertia. We sell when a company's returns start to fade, when management begins to buy growth through expensive acquisitions, or when the share price gets far ahead of what we think the business will earn. Last year we sold two holdings for the third reason. Both were good companies. Neither was a good investment at the price.</p>
<p class="article__disclaimer">This article reflects the author's views on the date of publication. It is not investment advice or a recommendation to buy or sell any security. Capital is at risk and past performance is not a guide to future returns.</p>
HTML;

$article_stewardship = <<<'HTML'
<p>Each September we publish a review of how we used our clients' votes over the previous 12 months. The full record, resolution by resolution, is available to every client on request. This is the summary.</p>
<p>In the year to 30 June 2026 we voted at 74 company meetings and on 1,108 resolutions. We supported management on 1,047 and voted against on 61, or 5.5%. We did not abstain on any resolution. We think an abstention tells a board nothing.</p>
<p>Pay was the largest single reason for opposition. We voted against 14 remuneration reports, mostly where bonuses had been paid in full in a year when shareholders had lost money, or where a new long-term incentive plan reset its targets lower without a clear reason. In each case we wrote to the chair of the remuneration committee before the meeting to explain our vote.</p>
<p>The second theme was board composition. We opposed the re-election of nine directors who sat on so many boards that we doubted they could give each one enough time. We also opposed the combined role of chair and chief executive at a UK engineering company we hold. After two meetings with the board, the company agreed to separate the roles, and an independent chair took up the post in May.</p>
<p>On climate, we voted on transition plans at six companies. We supported five. We opposed one, at a European materials business, because its targets for 2030 relied on technology it had not yet decided to buy. We have met the company twice since, and it will publish a revised plan next year.</p>
<p>Our bond holdings carry no votes, but they still give us a voice. In the credit strategy, we declined to buy three new bond issues during the year because the terms gave lenders too little protection if the company was sold. In one case the issuer improved the terms before the bonds were priced.</p>
<p>Stewardship is not a separate team at Wealth. The portfolio managers who own the shares make the voting decisions, because they know the companies well and they live with the results.</p>
<p class="article__disclaimer">This article reflects the author's views on the date of publication. It is not investment advice or a recommendation to buy or sell any security. Capital is at risk and past performance is not a guide to future returns.</p>
HTML;

$article_multi_asset = <<<'HTML'
<p>We do not make forecasts for the year ahead. We do think through what could happen, and make sure the portfolio can live with each outcome. This note sets out where the Global Multi-Asset Income Strategy stands and how we have thought about the next 12 months.</p>
<p>At the end of July the portfolio held 46% in equities, 38% in bonds, 11% in listed infrastructure and property, and 5% in cash. Its yield was around 4.6%.</p>
<p>The biggest change over the past year has been in bonds. For most of the strategy's early life, bonds paid so little that we held them mainly as protection. Now they contribute almost half of the portfolio's income. We have added four percentage points to gilts maturing in five to 10 years, which yield more than 4% and would rise in value if the economy weakened and rates were cut.</p>
<p>In equities we hold global dividend payers, with more in Europe, the UK and Japan than a global index would. Large American companies dominate world indices but pay relatively low dividends. We own some, chosen for the cash they return, but we do not try to match the index.</p>
<p>Listed infrastructure and property trade at discounts to the value of their assets that we think are too wide. Higher rates explain some of that. They do not explain why a portfolio of regulated electricity networks, with income linked to inflation, should trade 20% below what its assets would fetch in a private sale. We have added gradually and will continue to do so.</p>
<p>We have thought through three broad outcomes. If inflation falls and rates are cut, our bonds and infrastructure should do well, and the income on new bonds will drift lower. If inflation stays sticky and rates stay where they are, the portfolio's income holds up and equities matter most. The harder case is a return of rising inflation, when shares and bonds can fall together as they did in 2022. Our cash, our short-dated bonds and our inflation-linked infrastructure income are there for that outcome. They would soften a fall, not prevent one.</p>
<p>The aim, as always, is an income that holds up across the range, not a portfolio that is right about one outcome and badly wrong about the others.</p>
<p class="article__disclaimer">This article reflects the author's views on the date of publication. It is not investment advice or a recommendation to buy or sell any security. Capital is at risk and past performance is not a guide to future returns.</p>
HTML;

$article_central_banks = <<<'HTML'
<p>I spent 10 years writing about central banks for a living. The most useful thing I learned was how little most of what I wrote mattered six months later.</p>
<p>The Bank of England's Monetary Policy Committee meets eight times a year. Each meeting brings a decision, a set of minutes, a vote split and, four times a year, a full forecast. Each one moves markets. Gilt yields can shift by a tenth of a percentage point in an afternoon on a single phrase in the minutes, and then shift back within a week.</p>
<p>For an investor, that creates a temptation: to treat each meeting as a reason to act. We think that temptation is expensive. Trading costs money. It also takes time away from the work that drives returns over the long run, which is understanding the businesses and borrowers we lend to and own.</p>
<p>So we take a different approach, in three parts.</p>
<p>First, we decide ranges in advance. In the Short-Dated Sterling Credit Strategy, duration can move between one and three years. Within that range, the portfolio manager adjusts as conditions change. Over the past 18 months duration has moved twice, from 2.4 down to 1.7 years and then back up to 2.1, each time after a change in our view of where inflation was heading, not after a single meeting.</p>
<p>Second, we watch what changes the committee's mind, not what it says. Services inflation and pay growth have driven the Bank's decisions for the past three years. If they move decisively, policy will follow. A change of tone in a speech, without a change in the numbers, rarely lasts.</p>
<p>Third, we ask what is already in the price. By the time a cut is announced, markets have usually priced it for weeks. Acting on the announcement means buying what everyone else already owns.</p>
<p>None of this means ignoring central banks. They set the price of money, and that touches every asset we hold. It means listening carefully and changing course only when the evidence, not the commentary, says we should.</p>
<p class="article__disclaimer">This article reflects the author's views on the date of publication. It is not investment advice or a recommendation to buy or sell any security. Capital is at risk and past performance is not a guide to future returns.</p>
HTML;

$posts = [
	'why-income-still-matters' => [
		'author'   => 'eleanor-whitcombe',
		'category' => 'markets',
		'content'  => $article_income,
		'date'     => '2026-09-28 09:00:00',
		'excerpt'  => 'Cash and gilts pay a real yield again. So why own equity income at all? Because cash pays today, a good dividend can grow, and the difference compounds.',
		'sticky'   => true,
		'title'    => 'Why income still matters in a higher-rate world',
	],
	'case-for-short-dated-sterling-credit' => [
		'author'   => 'sophie-lindqvist',
		'category' => 'strategy',
		'content'  => $article_credit,
		'date'     => '2026-09-21 09:00:00',
		'excerpt'  => 'Short-dated sterling bonds now yield close to 5%, with far less interest-rate risk than longer credit. What investors are paid, what could go wrong, and how we are positioned.',
		'title'    => 'The case for short-dated sterling credit',
	],
	'quality-growth-in-europe' => [
		'author'   => 'daniel-okafor',
		'category' => 'strategy',
		'content'  => $article_europe,
		'date'     => '2026-09-14 09:00:00',
		'excerpt'  => 'The average European share is now held for months, not years. That impatience creates mispricings in steady compounders. Why we hold for five years or more, and what it costs.',
		'title'    => 'Quality growth in Europe: patience as an edge',
	],
	'stewardship-review-2026' => [
		'author'   => 'eleanor-whitcombe',
		'category' => 'stewardship',
		'content'  => $article_stewardship,
		'date'     => '2026-09-03 09:00:00',
		'excerpt'  => 'We voted on 1,108 resolutions in the year to 30 June 2026 and opposed management on 61. Where we disagreed, why, and what changed as a result.',
		'title'    => 'Stewardship and voting: our 2026 review',
	],
	'multi-asset-income-outlook' => [
		'author'   => 'tomas-herrera',
		'category' => 'markets',
		'content'  => $article_multi_asset,
		'date'     => '2026-08-25 09:00:00',
		'excerpt'  => 'Bonds pay a real income again, equities are uneven and infrastructure trades at a discount. How the Global Multi-Asset Income Strategy is positioned for the next 12 months, and why.',
		'title'    => 'Multi-asset income: the year ahead',
	],
	'reading-the-central-banks' => [
		'author'   => 'james-hartley',
		'category' => 'markets',
		'content'  => $article_central_banks,
		'date'     => '2026-08-12 09:00:00',
		'excerpt'  => 'The Bank of England meets eight times a year, and markets react to every word. Why we listen closely, change our portfolios rarely, and how we decide when to act.',
		'title'    => 'Reading the central banks without over-trading',
	],
];

$created_posts = 0;
foreach ( $posts as $slug => $post ) {
	[ $id, $c ] = wealth_seed_post( 'post', $slug, [
		'post_content' => $post['content'],
		'post_date'    => $post['date'],
		'post_excerpt' => $post['excerpt'],
		'post_title'   => $post['title'],
	], [
		'author_profile' => $team_ids[ $post['author'] ],
	], $img[ 'news-' . $slug . '.jpg' ] );

	wp_set_object_terms( $id, $post['category'], 'category' );

	if ( ! empty( $post['sticky'] ) ) {
		stick_post( $id );
	}

	$created_posts += $c;
}
echo "posts: {$created_posts} created, " . count( $posts ) . " total\n";

$options = [
	'address'                  => "Wealth Asset Management LLP\n14 King Street\nSt James's\nLondon SW1Y 6QU",
	'careers_email'            => 'careers@wealth-am.co.uk',
	'company_number'           => 'OC000000',
	'cta_default_body'         => 'Our team speaks directly to investors and their advisers. Tell us about your objectives and we will tell you plainly whether one of our strategies fits.',
	'cta_default_heading'      => 'Start a conversation',
	'email'                    => 'enquiries@wealth-am.co.uk',
	'footer_notice'            => 'Wealth Asset Management is a fictional firm created for this demonstration website. Nothing on this site is an offer, a recommendation or investment advice. Capital is at risk and the value of investments can fall as well as rise.',
	'legal_name'               => 'Wealth Asset Management LLP',
	'map_image'                => $img['map-st-james.jpg'],
	'people_archive_intro'     => "Wealth is owned by the people who work here, and the people who manage your capital are the people you meet. Our investment team and our executive team sit on one floor in St James's. Between them they have spent more than a century in markets, and none of them is in a hurry to leave.",
	'people_archive_title'     => 'People',
	'phone'                    => '+44 (0)20 7946 0123',
	'phone_href'               => '+442079460123',
	'press_email'              => 'press@wealth-am.co.uk',
	'registered_office'        => "14 King Street, St James's, London SW1Y 6QU",
	'social'                   => [
		[ 'icon' => 'linkedin', 'label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/company/wealth-asset-management' ],
		[ 'icon' => 'x', 'label' => 'X', 'url' => 'https://x.com/wealthassetmgmt' ],
	],
	'strategies_archive_intro' => 'We run four strategies and no more than we can manage well. Each draws on the same research and the same small team, and each has a clear limit on the assets it will take. All four are available as a segregated mandate or through a pooled fund for eligible investors, with every holding, cost and vote disclosed.',
	'strategies_archive_title' => 'Investment strategies',
];

foreach ( $options as $name => $value ) {
	update_option( $name, $value );
}
echo "options: " . count( $options ) . " set\n";

wealth_seed_menu( 'Primary', [
	[ 'menu-item-title' => 'About', 'menu-item-type' => 'custom', 'menu-item-url' => '/#about-us' ],
	[ 'menu-item-object' => 'strategy', 'menu-item-title' => 'Strategy', 'menu-item-type' => 'post_type_archive' ],
	[ 'menu-item-object' => 'team_member', 'menu-item-title' => 'People', 'menu-item-type' => 'post_type_archive' ],
	[ 'menu-item-object' => 'page', 'menu-item-object-id' => $news_id, 'menu-item-title' => 'News', 'menu-item-type' => 'post_type' ],
	[ 'menu-item-object' => 'page', 'menu-item-object-id' => $contact_id, 'menu-item-title' => 'Contact', 'menu-item-type' => 'post_type' ],
	[ 'menu-item-object' => 'page', 'menu-item-object-id' => $legal_id, 'menu-item-title' => 'Legal', 'menu-item-type' => 'post_type' ],
] );

wealth_seed_menu( 'Legal', [
	[ 'menu-item-object' => 'page', 'menu-item-object-id' => $legal_id, 'menu-item-title' => 'Important information', 'menu-item-type' => 'post_type' ],
	[ 'menu-item-object' => 'page', 'menu-item-object-id' => $privacy_id, 'menu-item-title' => 'Privacy', 'menu-item-type' => 'post_type' ],
	[ 'menu-item-object' => 'page', 'menu-item-object-id' => $cookies_id, 'menu-item-title' => 'Cookies', 'menu-item-type' => 'post_type' ],
	[ 'menu-item-object' => 'page', 'menu-item-object-id' => $accessibility_id, 'menu-item-title' => 'Accessibility', 'menu-item-type' => 'post_type' ],
] );

$locations = get_theme_mod( 'nav_menu_locations', [] );
foreach ( [ 'primary' => 'Primary', 'legal' => 'Legal' ] as $location => $menu_name ) {
	$menu = wp_get_nav_menu_object( $menu_name );
	if ( $menu ) {
		$locations[ $location ] = $menu->term_id;
	}
}
set_theme_mod( 'nav_menu_locations', $locations );
echo "menus: 2 ready, locations assigned\n";

update_option( 'blogdescription', 'Independent investment manager, London' );
update_option( 'blogname', 'Wealth' );
update_option( 'date_format', 'j F Y' );
update_option( 'page_for_posts', $news_id );
update_option( 'page_on_front', $front_id );
update_option( 'show_on_front', 'page' );
update_option( 'start_of_week', 1 );
update_option( 'time_format', 'H:i' );
update_option( 'timezone_string', 'Europe/London' );

update_option( 'permalink_structure', '/%postname%/' );
flush_rewrite_rules( false );

echo "settings: reading, identity, timezone, permalinks done\n";

foreach ( [ [ 'post', 'hello-world' ], [ 'page', 'sample-page' ], [ 'page', 'privacy-policy' ] ] as [ $default_type, $default_slug ] ) {
	foreach ( get_posts( [ 'name' => $default_slug, 'numberposts' => 1, 'post_status' => 'any', 'post_type' => $default_type ] ) as $default_post ) {
		wp_delete_post( $default_post->ID, true );
	}
}
echo "defaults: removed\n";
echo "SEED COMPLETE\n";
