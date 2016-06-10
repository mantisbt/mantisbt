# Basic makefile to build all MantisBT manuals

GUIDES = Admin_Guide Developers_Guide

.PHONY: subdirs $(GUIDES)
subdirs: $(GUIDES)

$(GUIDES):
	$(MAKE) -C $@
