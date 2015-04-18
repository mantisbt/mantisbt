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

# Output directory
BUILD_DIR:=build

# Installation directory
INSTALL_DIR:=install

# Extensions list
EXT_LIST:=html pdf ps rtf txt tar.gz

# Book name: will be used to build the input filename ($BOOK.sgml)
# and the output filename ($BOOK.html, $BOOK.pdf and so on)
BOOK:=administration_guide

# Stylesheet name. Original file from:
# /usr/share/sgml/docbook/utils-0.6.11/docbook-utils.dsl
#
STYLE=../../template/stylesheet.dsl
CSS=../../template/stylesheet.css

FILES:=$(BOOK).sgml $(wildcard *.sgml) $(STYLE) $(BUILD_DIR) builddate

IMAGES:=$(wildcard images/*.png)
EPSS:=$(IMAGES:.png=.eps)

default: help

all: html html_onefile html.tar.gz text pdf ps rtf

html: $(BUILD_DIR)/$(BOOK)/$(BOOK).html

html_onefile: $(BUILD_DIR)/$(BOOK).html

pdf: $(BUILD_DIR)/$(BOOK).pdf

ps: $(BUILD_DIR)/$(BOOK).ps

rtf: $(BUILD_DIR)/$(BOOK).rtf

text: $(BUILD_DIR)/$(BOOK).txt

$(BUILD_DIR):
	mkdir -p $@

builddate:
	echo -n $$(LANG="en_US";date "+%e %B %Y") > $@

html.tar.gz: html
	tar cz -f $(BUILD_DIR)/$(BOOK).tar.gz -C $(BUILD_DIR) $(BOOK)

$(BUILD_DIR)/$(BOOK)/$(BOOK).html: $(FILES) $(IMAGES)
	jw -d $(STYLE)#html -f docbook -b html -o $(BUILD_DIR)/$(BOOK) $<
	mkdir -p $(BUILD_DIR)/$(BOOK)/images
	-cp images/* $(BUILD_DIR)/$(BOOK)/images/
	-cp $(CSS) $(BUILD_DIR)/$(BOOK)/

$(BUILD_DIR)/$(BOOK).pdf: $(FILES) $(IMAGES)
	jw -d $(STYLE)#print -f docbook -b pdf -o $(BUILD_DIR) $<

$(BUILD_DIR)/$(BOOK).html: $(FILES) $(IMAGES)
	jw -d $(STYLE)#html -f docbook -b html -o $(BUILD_DIR) -u $<

$(BUILD_DIR)/$(BOOK).ps: $(FILES) $(EPSS)
	jw -d $(STYLE) -f docbook -b ps -o $(BUILD_DIR) $<

$(BUILD_DIR)/$(BOOK).rtf: $(FILES) $(IMAGES)
	jw -d $(STYLE) -f docbook -b rtf -o $(BUILD_DIR) $<

$(BUILD_DIR)/$(BOOK).txt: $(FILES)
	jw -f docbook -b txt -o $(BUILD_DIR) $<

# pattern rules to convert PNGs to EPS
#
%.eps : %.png
	pngtopnm $< | pnmtops -noturn > $@


.PHONY: valid install clean dist help
valid: builddate
	nsgmls -s $(BOOK).sgml

install:
	-mkdir -p $(INSTALL_DIR)
	-cp -r $(BUILD_DIR)/$(BOOK) $(foreach ext, $(EXT_LIST), $(BUILD_DIR)/$(BOOK).$(ext)) $(INSTALL_DIR)

clean:
	-$(RM) *.log *.dvi *.aux *.tex *.out
	-$(RM) $(GENERATED_IMG) builddate
	-$(RM) -r $(BUILD_DIR)/$(BOOK)
	-$(RM) $(BUILD_DIR)/$(BOOK).html $(BUILD_DIR)/$(BOOK).ps $(BUILD_DIR)/$(BOOK).pdf $(BUILD_DIR)/$(BOOK).rtf $(BUILD_DIR)/$(BOOK).txt

# Rebuild everything
dist: clean all

help:
	@echo "Usage: make <target>"
	@echo "Available targets are:"
	@echo "    help                  Show this text"
	@echo
	@echo "    html                  Convert to HTML (split pages)"
	@echo "    html.tar.gz           Convert to HTML (split pages, compressed archive)"
	@echo "    html_onefile          Convert to HTML (single page)"
	@echo "    pdf                   Convert to PDF"
	@echo "    ps                    Convert to Postscript"
	@echo "    rtf                   Convert to RTF"
	@echo "    text                  Convert to plain text"
	@echo "    all                   Convert to all of the above targets"
	@echo
	@echo "    clean                 Remove output and temporary files"
	@echo "    valid                 Check book correctness with nsgmls"
