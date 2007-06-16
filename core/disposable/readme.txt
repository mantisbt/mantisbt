==============================
   Disposable Email Checker
==============================

Disposable Email Checker is a library that allows applications to check for
users signup with disposable email addresses.  This is very important for
services that provide some trial period based on the email address.  Hence,
if users use disposable addresses, then they can potentially bypass the time
limit.  Other applications may use it to make sure that they have valid emails
that they can use for future correspondence.

In addition to providing a way to detect disposable addresses, this library 
also provides a way to classify these in several categories.  Some applications
may decide to accept some of these categories but not the other.  This provides
a great flexibility for each application to block the cases that doesn't work
for its domain.


Categories of disposable address
--------------------------------

Shredder Addresses - These are not really common, some other types end up becoming
a shredder emails, but these ones starts as such.  Shredder email addresses
delete all emails that are sent to it without storing them, forwarding them, 
or making them available to the user that created the address.  They are 
typically used in scenarios where users are required to provide a valid email
address but they are not expecting any valuable data or signup activation link
to be sent to it.

Forwarding Addresses - These are pretty common.  In this case users define a
disposable email address that forwards to their real address.  At any point in
time they may decide to deactivate this email address.  Some of these may still
accept the emails as if they are valid, but it is not forwarded anymore, and 
hence becoming a shredder address.  Some of these allow users to provision a
certain duration or number of emails after which the address expires.

Time Bound Addresses - These are a special kind of forwarding addresses that
expire after a time duration that is configured by the user.  After such 
duration the address becomes a shredder address.  This duration is typically
enough for a user to do the transaction that they required the address for.
For example, signup for a service, do a transaction with a shop, etc.

Free Email Boxes - The most common providers of these are the free web mail
providers.  The most commonly known are Hotmail, Gmail and Yahoo.  Although
these are free, they are not commonly used as disposable address.  Hence,
although this library provides a check for them, they are not considered
disposable.  Blocking these is likely to block a lot of legitimate users.


How Disposable Addresses are detected?
--------------------------------------

This library has a list of rules that are used to determine whether an address 
is disposable or not.  The library does not connect to the Internet to determine 
the kind or the validity of the address.  The library may be enhanced in the 
future to provide applications with ways of explicitly requiring online checks.


Contributing
------------

This library is available as open source with LGPL license, so you can use it
in both open source and commercial applications.  The best ways to contribute
back to this library are:

1. Report service providers that the library should detect but it doesn't.
2. Report bugs and feature request in the bug tracker.
3. Provide ports for the library in languages other than PHP.

To report bugs and feature requests use the bug tracker at:
http://www.futureware.biz/mantis


Versioning Scheme
-----------------

The versioning for this library is formatted as follows "1.2.3".

   (1) This is the majro version which will change when there are major changes 
       or re-implementation.
       
   (2) This is the minor version which will change when the APIs changes.
       Most of the time these won't be breaking changes, but sometimes they
       may be.
       
   (3) This is the data version which is the only one changed in releases that
       just update the rules that are used to determine that an email address
       is disposable.
