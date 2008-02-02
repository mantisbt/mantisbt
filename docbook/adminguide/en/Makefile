# Makefile for DocBoook compilation
# 
# $Id: Makefile,v 1.5 2004/10/01 12:54:37 giallu Exp $
# 
#
# Some things to remember:
# - The main DocBook name is $BOOK.sgml
# - All other files should have .sgml extension
# - Images should go into ./images directory
# - Images should be in png format
# 


# Book name: will be used to build the input filename ($BOOK.sgml)
# and the output filename ($BOOK.html, $BOOK.pdf and so on)
BOOK=administration_guide

# Stylesheet name. Original file from:
# /usr/share/sgml/docbook/utils-0.6.11/docbook-utils.dsl
#
STYLE=stylesheet.dsl

FILES:=$(BOOK).sgml $(wildcard *.sgml) $(STYLE) builddate

IMAGES:=$(wildcard images/*.png)
EPSS:=$(IMAGES:.png=.eps)

default: help

all: html pdf
#all: html html_onefile text pdf ps sgml.gz html.tar.gz

html: $(BOOK)/$(BOOK).html 

html_onefile: $(BOOK).html

pdf: $(BOOK).pdf

ps: $(BOOK).ps

rtf: $(BOOK).rtf

text: $(BOOK).txt

builddate:
	echo -n $$(LANG="en_US";date "+%e %B %Y") > $@

html.tar.gz: html
	(for i in $(files); do \
		tar -cf $$i.tar $$i; \
		gzip -f $$i.tar; \
	done; )

$(BOOK)/$(BOOK).html: $(FILES) $(IMAGES)
	jw -d $(STYLE)#html -f docbook -b html -o $(BOOK) $<
	-cp -r images $(BOOK)/

$(BOOK).pdf: $(FILES) $(IMAGES)
	jw -d $(STYLE)#print -f docbook -b pdf $<

$(BOOK).html: $(FILES) $(IMAGES)
	jw -d $(STYLE)#html -f docbook -b html -u $<
	
$(BOOK).ps: $(FILES) $(EPSS)
	jw -d $(STYLE) -f docbook -b ps $<

$(BOOK).rtf: $(FILES) $(IMAGES)
	jw -d $(STYLE) -f docbook -b rtf $<

$(BOOK).txt: $(FILES)
	jw -f docbook -b txt $<

# pattern rules to convert PNGs to EPS
# 
%.eps : %.png
	pngtopnm $< | pnmtops -noturn > $@


.PHONY: valid clean dist help
valid:
	nsgmls -s $(BOOK).sgml

clean:
	-$(RM) *.log *.dvi *.aux *.tex *.out
	-$(RM) $(GENERATED_IMG) builddate
	-$(RM) -r $(BOOK)
	-$(RM) $(BOOK).html $(BOOK).ps $(BOOK).pdf $(BOOK).rtf $(BOOK).txt

# Rebuild everything
dist: clean all

help:
	@echo "Usage: make <target>"
	@echo "Available targets are:"
	@echo "    help                  Show this text"
	@echo "    html                  Convert to HTML (split pages)"
	@echo "    html_onefile          Convert to HTML (single page)"
	@echo "    pdf                   Convert to PDF"
	@echo "    rtf                   Convert to RTF"
	@echo "    ps                    Convert to Postscript"
	@echo "    text                  Convert to plain text"
	@echo "    clean                 Remove output and temporary files"
	@echo "    valid                 Check book correctness with nsgmls"
