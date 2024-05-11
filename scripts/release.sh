#!/bin/bash

# Exit if any command fails
set -e

# Check if the git working directory is dirty
if [ -n "$(git status --porcelain)" ]; then
    echo "Your git directory is dirty. Please commit or stash your changes before running this script."
    exit 1
fi

# Default to 'minor' if no argument is provided
versionType=${1:-minor}

# Path to the plugin file
file="ktt-wp-recommended-plugins.php"

# Extract the current version number, accounting for variable whitespace
currentVersion=$(grep "Version:" $file | sed -e 's/.*Version:[[:space:]]*//')
echo "Current Version: $currentVersion"

# Split the version number into major, minor, and patch
IFS='.' read -r -a versionParts <<< "$currentVersion"

major=${versionParts[0]}
minor=${versionParts[1]}
patch=${versionParts[2]}

# Increment the version based on the input parameter
case $versionType in
    major)
        major=$((major+1))
        minor=0
        patch=0
        ;;
    minor)
        minor=$((minor+1))
        patch=0
        ;;
    patch)
        patch=$((patch+1))
        ;;
    *)
        echo "Invalid version type specified. Use 'major', 'minor', or 'patch'."
        exit 1
        ;;
esac

# Construct the new version string
newVersion="$major.$minor.$patch"
echo "New Version: $newVersion"

# Replace the version in the file, accounting for variable whitespace around the version number
sed -i'' -e "s/\(Version:[[:space:]]*\)[0-9]*\.[0-9]*\.[0-9]*/\1$newVersion/" $file

# Add the changes to git, commit, and tag with the new version number
git add $file
git commit -m "Update version to $newVersion"
git tag -a "$newVersion" -m "Release version $newVersion"

# Push the changes and tags to the remote repository
git push
git push --tags

echo "Git commit and tag for version $newVersion created and pushed."

echo ""
echo "To create a new release, click:"
echo "https://github.com/KeesCBakker/ktt-wp-recommended-plugins/releases/new?tag=$newVersion&title=KeesTalksTech%20Code%20v$newVersion"
echo ""
