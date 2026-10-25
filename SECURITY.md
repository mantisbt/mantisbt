# Security Policy

## Supported Versions

Only the [latest 2.x stable release](https://mantisbt.org/download.php) 
is fully supported and receives security and bug fixes.

Earlier versions (including the legacy 1.3.x series) are no longer supported.


## Reporting a Vulnerability

If you discover a security issue (or what you think could be one), please 
[open a new issue](https://mantisbt.org/bugs/bug_report_page.php?category_id=36&view_state=50) 
in our bug tracker following the guidelines below.

> [!TIP]
> Please note that you must sign up and be logged in with your mantisbt.org account to report issues.
> If for some reason you are not able to create an account or login, you may also reach the developers
> on our [Gitter channel](https://matrix.to/#/#mantisbt_mantisbt:gitter.im).


> [!WARNING]
> **Do not submit GitHub Pull Requests or post any details on the mailing list !**  
> These are public channels, using them would effectively disclose the vulnerability.

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
4. Provide a descriptive **Summary** and clear **Description** of the issue.
5. Do not forget detailed **Steps To Reproduce**
   to facilitate our work in analyzing and fixing the problem
6. Additional, nice-to-have information
   * Severity assessment, ideally in the form of a
     **[CVSS v4](https://www.first.org/cvss/calculator/4.0) Vector**
     (only *Base Metrics* are required).
   * Identify any applicable **Weaknesses**, using the standard
     [Common Weakness Enumeration ID](https://cwe.mitre.org/)
     (e.g. CWE-79 for XSS).
8. If you already have a **patch** for the issue, please attach it to the issue
10. Provide your **GitHub username**

① These fields will be preset if you use the provided 
[link](https://mantisbt.org/bugs/bug_report_page.php?category_id=36&vie&view_state=50).


### CVE handling

To ensure a comprehensive, consistent and detailed declaration of the issue, 
we prefer requesting CVE IDs ourselves. 

After analyzing the report and confirming the vulnerability, we will open a
[Security Advisory](https://github.com/mantisbt/mantisbt/security/advisories/),
to which you will be granted access if we know your GitHub username.

A CVE ID request will be sent to GitHub through that channel. 
In case you have already obtained a CVE, do not forget to reference its ID in the bug report.


### Patching

Collaboration on a patch to fix the vulnerability, including review and testing, 
will take place on a private GitHub repository.


### Credits

Should you wish to be credited for the finding, kindly indicate it in the 
Issue Report under **Additional Information**, or in a bug note.
Your name/e-mail/company will be included in the CVE report as specified.


For further information, please refer to the 
[MantisBT Wiki](https://mantisbt.org/wiki/doku.php/mantisbt:handling_security_problems).
