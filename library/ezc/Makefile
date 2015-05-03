# Build the ezComponents autoload directory


# Directory name
AUTOLOAD := autoload

# List autoload scripts from available components
FILELIST != find -name "*_$(AUTOLOAD).php" ! -path "./$(AUTOLOAD)/*"

# Copy autoload scripts
copyfiles: $(AUTOLOAD)
	cp -v $(FILELIST) $(AUTOLOAD)

# Create directory
$(AUTOLOAD):
	mkdir $(AUTOLOAD)
