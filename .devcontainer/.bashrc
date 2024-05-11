# figure out workspace 
export WORKSPACE_NAME=$(pwd | awk -F'/' '{for(i=1;i<=NF;i++) if ($i == "workspaces") {print $(i+1); exit}}')
export WORKSPACE_SCRIPT_PATH="/workspaces/$WORKSPACE_NAME/scripts"

# make scripts executable
pushd $WORKSPACE_SCRIPT_PATH > /dev/null
sudo chmod +x *.sh
popd > /dev/null

export PATH="$PATH:$WORKSPACE_SCRIPT_PATH"

# make aliases for easy execution
# alias script_name='script_name.sh'
alias release='release.sh'

# make sure our .wp-now is present to cache
mkdir -p "$WORKSPACE_SCRIPT_PATH/../.wp-now"

# let the user know
echo ""
echo "To release a new version do:"
echo "\$ release [patch|minor|major]"
echo ""
echo "To start the project:"
echo "\$ wp-now start"
echo ""
