# Security Policy

## Supported Versions

Only the [latest 2.x stable release](https://mantisbt.org/download.php) 
is fully supported and receives security and bug fixes.

The legacy 1.3.x series is no longer actively supported, 
and only gets fixes for critical issues and vulnerabilities.

Earlier releases (1.2.x and older) are not supported anymore.

## Reporting a Vulnerability

If you discover a security issue (or what you think could be one), please 
[open a new issue](https://mantisbt.org/bugs/bug_report_page.php?category_id=36&view_state=50) 
in our bug tracker following the guidelines below.
Please note that you must sign up and be logged in with your 
mantisbt.org account to report issues.

:warning: **Do not submit GitHub Pull Requests or post on the mailing list** :warning:  
These are public channels which would effectively disclose the security issue.

One of the core team members will review, reply and ask for additional information as required. 
We will then discuss the means of fixing the vulnerability and agree on a calendar for disclosure. 
Generally this discussion takes place within the issue itself, but in some cases it may happen 
privately, e.g. by e-mail.

1. Set **Category** to _security_ ①
2. Make sure that **View Status** is set to _Private_ ①;
   this will hide your report from the general public, and
   only MantisBT developers will have access to it.
3. Set the **Product Version** as appropriate; 
   if necessary (e.g. when multiple versions are affected), 
   include additional information in the Description field or in a bugnote.
4. Provide a descriptive **Summary** and clear **Description** of the issue
5. Do not forget detailed **Steps To Reproduce** 
   to facilitate our work in analyzing and fixing the problem
6. If you already have a **patch** for the issue, please attach it to the issue

① These fields will be preset if you use the provided 
[link](https://mantisbt.org/bugs/bug_report_page.php?category_id=36&vie&view_state=50).

### CVE handling

To ensure a comprehensive, consistent and detailed declaration of the issue, 
we generally prefer requesting CVE IDs ourselves. 
The request is usually sent to MITRE after we have analyzed the issue and
confirmed the vulnerability. 

Should you wish to be credited for the finding, kindly indicate it under 
**Additional Information** or in a bug note.
Your name/e-mail/company will be included in the CVE report as specified.

In case you have already obtained a CVE, do not forget to reference its ID in the bug report

For further information, please refer to the 
[MantisBT Wiki](https://mantisbt.org/wiki/doku.php/mantisbt:handling_security_problems).
