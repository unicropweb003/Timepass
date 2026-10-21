@extends('layouts.app', ['seo' => \App\Support\Seo::privacy()])

@section('content')
@php
    $updated = '21 October 2026';
    $email   = config('shop.contact.email');
    $phone   = config('shop.contact.phone');
    $address = config('shop.contact.address');
    $sections = [
        'who-we-are' => 'Who we are',
        'what-we-collect' => 'Information we collect',
        'how-we-use' => 'How we use your information',
        'sharing' => 'Who we share it with',
        'cookies' => 'Cookies',
        'retention' => 'How long we keep it',
        'your-rights' => 'Your rights and choices',
        'delete-account' => 'Delete your account',
        'security' => 'Security',
        'children' => 'Children',
        'changes' => 'Changes to this policy',
        'contact-us' => 'Contact us',
    ];
@endphp

<div class="mx-auto max-w-3xl">

    {{-- Page heading --}}
    <div class="text-center">
        <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-600">
            Legal
        </span>
        <h1 class="mt-4 text-4xl font-bold tracking-tight text-gray-900">Privacy Policy</h1>
        <p class="mx-auto mt-3 max-w-xl text-gray-600">
            How TimePass collects, uses and protects your personal data on our website and Android app.
        </p>
        <p class="mt-2 text-sm text-gray-500">Last updated: {{ $updated }}</p>
    </div>

    {{-- Short summary --}}
    <div class="mt-10 rounded-2xl border border-indigo-100 bg-indigo-50/60 p-6">
        <p class="text-sm font-semibold text-indigo-900">The short version</p>
        <ul class="mt-3 list-disc space-y-1.5 pl-5 text-sm leading-relaxed text-indigo-950/80 marker:text-indigo-400">
            <li>We collect only what we need to run your account and deliver your orders.</li>
            <li>Card, UPI and bank details go straight to Razorpay, our payment partner, never to our servers.</li>
            <li>We don't sell your data and we don't use advertising or tracking cookies.</li>
            <li>You can edit your details any time and ask us to <a href="#delete-account" class="font-semibold underline underline-offset-2">delete your account</a>.</li>
        </ul>
    </div>

    {{-- Contents --}}
    <nav aria-label="Contents" class="mt-8 rounded-2xl border border-gray-200 bg-white p-6">
        <p class="text-sm font-semibold text-gray-900">Contents</p>
        <ol class="mt-3 grid list-decimal gap-x-8 gap-y-1.5 pl-5 text-sm text-gray-600 marker:text-gray-400 sm:grid-cols-2">
            @foreach ($sections as $id => $title)
                <li><a href="#{{ $id }}" class="transition hover:text-indigo-600">{{ $title }}</a></li>
            @endforeach
        </ol>
    </nav>

    <article class="mt-10 space-y-12 text-[15px] leading-relaxed text-gray-700
        [&_h2]:scroll-mt-28 [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:tracking-tight [&_h2]:text-gray-900
        [&_h3]:mt-6 [&_h3]:font-semibold [&_h3]:text-gray-900
        [&_p]:mt-3 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:pl-5 [&_ul]:marker:text-indigo-400
        [&_ol]:mt-3 [&_ol]:list-decimal [&_ol]:space-y-2 [&_ol]:pl-5 [&_ol]:marker:font-semibold [&_ol]:marker:text-indigo-500
        [&_strong]:font-semibold [&_strong]:text-gray-900
        [&_a]:font-medium [&_a]:text-indigo-600 [&_a]:underline [&_a]:decoration-indigo-200 [&_a]:underline-offset-2 [&_a:hover]:text-indigo-700">

        <section>
            <h2 id="who-we-are">1. Who we are</h2>
            <p>
                TimePass ("TimePass", "we", "us") runs the TimePass online beauty and personal care store at
                <a href="{{ route('home') }}">{{ preg_replace('#^https?://#', '', rtrim(route('home'), '/')) }}</a>
                and the TimePass Android app (together, "the Service").
            </p>
            <p>
                This policy explains what personal data we collect when you use the Service, why we collect it, who we share it with
                and the choices you have. We are based in India and handle personal data in line with Indian law, including the
                Digital Personal Data Protection Act, 2023 and the Information Technology Act, 2000.
            </p>
        </section>

        <section>
            <h2 id="what-we-collect">2. Information we collect</h2>

            <h3>Information you give us</h3>
            <ul>
                <li><strong>Account details:</strong> your name, mobile number, email address and password when you register. Passwords are stored only in hashed (scrambled) form, so we can't read them.</li>
                <li><strong>Delivery addresses:</strong> the recipient name, phone number, address, city, state and PIN code that you save or use for an order.</li>
                <li><strong>Orders:</strong> the products you buy, prices, discounts, delivery charges, the delivery address, order status and courier tracking details.</li>
                <li><strong>Payments:</strong> the payment method (for example UPI, card, net banking or cash on delivery), amount, status and the reference numbers our payment partner sends us. Card numbers, CVVs, UPI PINs and bank passwords are entered directly on Razorpay's secure checkout and never reach our servers.</li>
                <li><strong>Wishlist:</strong> the products you save for later.</li>
                <li><strong>Messages:</strong> your name, email address, phone number (optional), topic and message when you contact us.</li>
            </ul>

            <h3>Information collected automatically</h3>
            <ul>
                <li><strong>Sign-in sessions:</strong> when you use the Service we keep a session record with your IP address and browser or device type, so we can keep you signed in securely.</li>
                <li><strong>Contact form:</strong> we record the IP address a message was sent from, to help stop spam and abuse.</li>
                <li><strong>Server logs:</strong> our hosting provider records standard request information (such as IP address, the page requested and the time) for security and troubleshooting.</li>
            </ul>
            <p>
                We don't ask for your precise location, contacts, photos or files, and we don't use advertising IDs, analytics trackers or advertising cookies.
            </p>
        </section>

        <section>
            <h2 id="how-we-use">3. How we use your information</h2>
            <ul>
                <li>To create and manage your account and keep you signed in.</li>
                <li>To process, take payment for, pack, ship and deliver your orders, and to show you your order history and transactions.</li>
                <li>To handle returns, refunds and customer support, and to reply to your messages.</li>
                <li>To contact you about your account or orders when needed.</li>
                <li>To keep the Service secure, including preventing fraud, spam and misuse, and blocking abusive accounts.</li>
                <li>To meet our legal, tax and accounting obligations.</li>
            </ul>
            <p><strong>We do not sell your personal data, and we do not use it for third-party advertising.</strong></p>
        </section>

        <section>
            <h2 id="sharing">4. Who we share it with</h2>
            <p>We share personal data only with service providers that help us run the store, and only the data they need:</p>
            <ul>
                <li><strong>Razorpay (payments):</strong> the order amount, plus your email address and phone number to pre-fill checkout. The payment details you enter are collected by Razorpay under its own <a href="https://razorpay.com/privacy/" target="_blank" rel="noopener noreferrer">privacy policy</a>.</li>
                <li><strong>Delivery partners:</strong> the recipient name, phone number and delivery address needed to deliver your order.</li>
                <li><strong>Hosting and database providers:</strong> the Service and its database run on cloud infrastructure (Render and Neon) that stores data on our behalf. These providers may store data on servers outside India.</li>
                <li><strong>Fonts and page styling:</strong> our pages load fonts from Google Fonts and styling code from the Tailwind CSS content delivery network. Your browser sends these services your IP address and browser details when it requests these files.</li>
                <li><strong>Legal reasons:</strong> government or law-enforcement authorities when the law requires it, or when needed to protect the rights, property or safety of TimePass, our customers or others.</li>
            </ul>
            <p>If TimePass is ever merged with or sold to another business, your data may be transferred as part of that deal, and it will stay protected by this policy.</p>
        </section>

        <section>
            <h2 id="cookies">5. Cookies</h2>
            <p>We use only the cookies the Service needs to work:</p>
            <ul>
                <li><strong>Session cookie:</strong> keeps you signed in and remembers your session. It expires after a period of inactivity or when you log out.</li>
                <li><strong>Security cookie (XSRF-TOKEN):</strong> protects forms against cross-site request forgery.</li>
                <li><strong>"Remember me" cookie:</strong> set only if you tick <em>Remember me</em> when logging in, and keeps you signed in on that device until you log out.</li>
            </ul>
            <p>We don't use advertising, tracking or analytics cookies. If you block cookies in your browser, you won't be able to sign in or place orders.</p>
        </section>

        <section>
            <h2 id="retention">6. How long we keep it</h2>
            <ul>
                <li><strong>Account details, saved addresses and wishlist:</strong> while your account is active. They are deleted when you delete your account, and you can remove saved addresses yourself at any time.</li>
                <li><strong>Order and payment records:</strong> for as long as Indian tax and accounting laws require (currently up to 8 years), even after you delete your account.</li>
                <li><strong>Contact messages:</strong> while we deal with your enquiry, after which we delete them when they're no longer needed. You can ask us to delete yours at any time.</li>
                <li><strong>Sign-in sessions:</strong> they expire automatically after a period of inactivity or when you log out.</li>
                <li><strong>Server logs:</strong> kept by our hosting provider for a limited period for security and troubleshooting.</li>
            </ul>
        </section>

        <section>
            <h2 id="your-rights">7. Your rights and choices</h2>
            <ul>
                <li><strong>See and correct your data:</strong> update your name, mobile number, email address and password in <a href="{{ route('account.profile') }}">My Account &rsaquo; Edit Profile</a>, manage addresses in <a href="{{ route('account.addresses') }}">Delivery Addresses</a>, and view your <a href="{{ route('account.orders') }}">orders</a> and <a href="{{ route('account.transactions') }}">transactions</a>.</li>
                <li><strong>Get a copy:</strong> ask us for a summary of the personal data we hold about you.</li>
                <li><strong>Erase:</strong> ask us to delete your account and personal data (see <a href="#delete-account">Delete your account</a>).</li>
                <li><strong>Withdraw consent:</strong> you can stop using the Service and ask us to delete your data at any time. This won't affect anything we did lawfully before you withdrew.</li>
                <li><strong>Nominate:</strong> you can nominate another person to exercise these rights for you in the event of your death or incapacity.</li>
                <li><strong>Complain:</strong> raise a grievance with us using the details in <a href="#contact-us">Contact us</a>. If you're not satisfied with our response, you can approach the Data Protection Board of India.</li>
            </ul>
            <p>We reply to privacy requests within 30 days. We may ask you to confirm your identity before acting on a request.</p>
        </section>

        <section>
            <h2 id="delete-account">8. Delete your TimePass account</h2>
            <p>You can ask us to delete your TimePass account and the personal data linked to it at any time, whether you signed up on the website or in the Android app.</p>

            <h3>How to request deletion</h3>
            <ol>
                <li>
                    @if ($email)
                        Email <a href="mailto:{{ $email }}?subject={{ rawurlencode('Delete my TimePass account') }}">{{ $email }}</a> from the email address on your account, with the subject "Delete my TimePass account",
                    @else
                        Send us a request
                    @endif
                    or use our <a href="{{ route('contact', ['subject' => 'Privacy or account deletion']) }}">contact form</a> and choose "Privacy or account deletion". Include the name, email address and mobile number on your account.
                </li>
                <li>We may ask you to confirm that the account is yours, for example by replying from your registered email address.</li>
                <li>We delete your account within 30 days and email you to confirm. Any orders still in progress must be delivered or cancelled first.</li>
            </ol>

            <h3>What we delete</h3>
            <p>Your name, email address, mobile number, password, saved delivery addresses, wishlist, sign-in sessions and the messages you sent us.</p>

            <h3>What we keep, and for how long</h3>
            <p>
                Records of orders you placed and payments you made, including the delivery details on those orders, are kept for as long as
                Indian tax and accounting laws require (currently up to 8 years) and used only for legal, tax, accounting, fraud-prevention
                and dispute purposes. After that period they are deleted.
            </p>
        </section>

        <section>
            <h2 id="security">9. Security</h2>
            <p>
                We protect your data with HTTPS encryption, hashed passwords and restricted access to our admin systems. Payments are
                handled by Razorpay, a PCI-DSS compliant payment gateway, so we never store your card or bank details. No method of
                storing or sending data online is completely secure, so if you think your account has been accessed without your
                permission, please contact us straight away.
            </p>
        </section>

        <section>
            <h2 id="children">10. Children</h2>
            <p>
                The Service is meant for adults aged 18 and over. Anyone under 18 may use it only with the involvement and consent of a
                parent or guardian. We don't knowingly collect personal data from children without verifiable parental consent. If you
                believe a child has given us personal data, contact us and we will delete it.
            </p>
        </section>

        <section>
            <h2 id="changes">11. Changes to this policy</h2>
            <p>
                We may update this policy from time to time. When we do, we'll change the "Last updated" date at the top of this page,
                and for significant changes we'll let you know on the website, in the app or by email.
            </p>
        </section>

        <section>
            <h2 id="contact-us">12. Contact us</h2>
            <p>For questions, requests or complaints about your personal data, contact our grievance team:</p>
            <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-6 text-sm">
                <p class="!mt-0 font-semibold text-gray-900">TimePass Store</p>
                <dl class="mt-3 space-y-2">
                    @if ($email)
                        <div class="flex gap-2"><dt class="w-16 shrink-0 text-gray-500">Email</dt><dd><a href="mailto:{{ $email }}" class="break-all">{{ $email }}</a></dd></div>
                    @endif
                    @if ($phone)
                        <div class="flex gap-2"><dt class="w-16 shrink-0 text-gray-500">Phone</dt><dd><a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a></dd></div>
                    @endif
                    @if ($address)
                        <div class="flex gap-2"><dt class="w-16 shrink-0 text-gray-500">Address</dt><dd>{{ $address }}</dd></div>
                    @endif
                    <div class="flex gap-2"><dt class="w-16 shrink-0 text-gray-500">Online</dt><dd><a href="{{ route('contact') }}">Contact form</a></dd></div>
                </dl>
            </div>
        </section>
    </article>
</div>
@endsection
