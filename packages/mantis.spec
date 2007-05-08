# %global pkgdir		%_datadir/%name
%global pkgdir		/var/www/%name
%global cfgdir		%_sysconfdir/%name
%global httpconfdir	%_sysconfdir/httpd/conf.d

Summary: Mantis Bugtracker
Name: mantis
Version: 1.1.0a3
Release: 20051130
License: GPL
Group: Applications/Internet
URL: http://www.mantisbugtracker.com/
BuildArch: noarch
Source0: http://download.sourceforge.net/sourceforge/mantisbt/%name-%version-%release.tar.gz
Source1: mantis-httpd.conf
BuildRoot: %_tmppath/%name-%version-%release-buildroot
BuildRequires: diffutils
Requires: php


%description
Mantis is a web-based bugtracking system. It is written in the PHP 
scripting language and requires the MySQL database and a webserver. 
Mantis has been installed on Windows, MacOS, OS/2, and a variety of 
Unix operating systems. Any web browser should be able to function 
as a client. It is released under the terms of the GNU General 
Public License (GPL).


%prep
%setup -n %{name}-%{version}-%{release} -q

chmod -x *.php
rm -rf packages


%install
rm -rf "$RPM_BUILD_ROOT"
%__install -d -m755 $RPM_BUILD_ROOT%pkgdir
%__install -d -m750 $RPM_BUILD_ROOT%cfgdir

tar cf - . | tar xf - -C $RPM_BUILD_ROOT%pkgdir

find $RPM_BUILD_ROOT \( \
	-name '*.noexamplecom' -o -name '*.iis' -o -name '*.noadmin' -o -name '*.#.*' -o \
	-name '.cvsignore' \
	\) -print0 | xargs -0 rm -f

## Do not rename; the *existence* of this file will be checked to
## determine if mantis is offline
mv $RPM_BUILD_ROOT%pkgdir/mantis_offline.php.sample $RPM_BUILD_ROOT%cfgdir/
mv $RPM_BUILD_ROOT%pkgdir/config_inc.php.sample     $RPM_BUILD_ROOT%cfgdir/config_inc.php

ln -s %cfgdir/config_inc.php	 $RPM_BUILD_ROOT%pkgdir/config_inc.php
ln -s %cfgdir/mantis_offline.php $RPM_BUILD_ROOT%pkgdir/mantis_offline.php

## The httpd config-files
function subst() {
	f=$RPM_BUILD_ROOT$1
	sed -e 's!/usr/share/mantis!%pkgdir!g' "$f" >"$f".tmp
	cmp -s "$f" "$f.tmp" || cat "$f.tmp" >"$f"
	rm -f "$f.tmp"
}

%__install -d $RPM_BUILD_ROOT%httpconfdir
%__install -p -m644 %SOURCE1 $RPM_BUILD_ROOT%httpconfdir/mantis.conf
subst %httpconfdir/mantis.conf


%clean
rm -rf "$RPM_BUILD_ROOT"


%post
#/etc/init.d/httpd restart


%files
%defattr(-,root,root,-)
%doc doc/*
%pkgdir
%attr(-,root,apache) %dir %cfgdir
%attr(0640,root,apache) %config(noreplace) %cfgdir/*
%config(noreplace) %httpconfdir/*


%changelog
* Wed Dec 03 2005 Victor Boctor
- Updated summary, description and URL.  Also added to CVS.

* Wed Nov 30 2005 Iain Lea <iain@bricbrac.de> - 1.1.0-cvs
- updated to build single RPM from 1.1.0-cvs

* Sat Jun 25 2005 Enrico Scholz <enrico.scholz@informatik.tu-chemnitz.de> - 1.0.0
- updated to 1.0.0a3
- removed the part which created the psql-script; upstream has now a
  working PostgreSQL database creation script
- rediffed the -iis patch
- added patch to make upgrade functionionality partially working with
  PostgreSQL; this is not perfect as things like index creation will
  still fail

* Thu May 19 2005 Enrico Scholz <enrico.scholz@informatik.tu-chemnitz.de> - 0.19.2-2
- use %%dist instead of %%disttag

* Mon Mar  7 2005 Enrico Scholz <enrico.scholz@informatik.tu-chemnitz.de> - 0.19.2-1
- updated to 0.19.2
- rediffed patches
- removed dependency on php-mysql as it supports PostgreSQL also
- added inline-hack to generate a PostgreSQL database creation script

* Thu May 27 2004 Enrico Scholz <enrico.scholz@informatik.tu-chemnitz.de> - 0:0.18.3-0.fdr.2
- ship doc/ in the program-directory instead of copying it into %%docdir
- modified shipped httpd configuration to disable admin/ directory
  explicitly and added some documentation there
- added noadmin patch to disable warning about existing admin/ directory;
  since this directory is disabled by httpd configuration
- lower restrictions on the required 'mantis-config' subpackage; use
  descriptive names as version instead of EVR
- restart 'httpd' after the upgrade
- preserve timestamps of the configuration files to avoid creation of
  .rpmnew files on every update

* Tue May 25 2004 Enrico Scholz <enrico.scholz@informatik.tu-chemnitz.de> - 0:0.18.3-0.fdr.0.1
- updated to 0.18.2
- rediffed the patches

* Fri Aug 15 2003 Enrico Scholz <enrico.scholz@informatik.tu-chemnitz.de> 0:0.18.0-0.fdr.0.2.a4
- use generic download-address for Source0

* Thu Jun 19 2003 Enrico Scholz <enrico.scholz@informatik.tu-chemnitz.de> 0:0.18.0-0.fdr.0.1.a4
- applied the Fedora naming standard

* Thu Jun 19 2003 Enrico Scholz <enrico.scholz@informatik.tu-chemnitz.de> 0:0.18.0-0.fdr.0.a4.2
- Initial build.
