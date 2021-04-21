# Upgrade to d9:

## Is this for me?:

1.  Do you have a Drupal 8 site hosted at Pantheon?

1.  Is your site a member of a pantheon "organization"? Not a dealbreaker either way, but you need to know.

    ```terminus site:info ${OLD_SITE_NAME} --format=json | jq -r ".organization"```

2.  If so, do you have the permissions to create new sites inside that org? If the answer is "No", you need that
    permission to use this guide.

2.  Do you have administrative permissions in Pantheon to move things like DNS names and primary site URL's?
    You can create the upgrade, but you will not be able to make the site live without permission to change
    those settings.


## Installed Tools:

1.  Install a standard set of open source command line tools:

    a. Apple Command Line Tools (```xcode-select --install```) + (homebrew)[https://brew.sh]

    OR

    b. (Windows Subsystem For Linux)[https://docs.microsoft.com/en-us/windows/wsl/install-win10]

2. install the command line utilities:  jq terminus direnv

   // TODO: commands to install for win/mac

## Step 1:

1. Authenticate terminus:

  - ```terminus auth:login --email={YOUR PANTHEON USER EMAIL ADDRESS}```

All the instructions will be based on this login information and user space.
If you stop or pause in the middle, you may have to login again. Terminus logins
expire about every 24 hours.


## VARIABLES:

We're going to export our current site's pantheon site name. Terminus will use this site name
act on your behalf in the pantheon dashboard.

```export OLD_SITE_NAME=d8-test-site```

Then run the other lines to build names based on the old site's name.

```
export NEW_SITE_NAME=${OLD_SITE_NAME}-$(date +%Y)
export OLD_SITE_LABEL=$(terminus site:info ${OLD_SITE_NAME} --format=json | jq -r ".label")
export NEW_SITE_LABEL="${OLD_SITE_LABEL} $(date +%Y)"
export ORGANIZATION=$(terminus site:info ${OLD_SITE_NAME} --format=json | jq -r ".organization")
// TODO: try this next command for sites with and without an org
// ${ORG_COMMAND_SWITCH} should be empty if your site is not a member of an org
// and --org=ORG_ID if your site is a member of an org.
export ORG_COMMAND_SWITCH=$([[ ! -z "${ORGANIZATION}" ]] && echo "--org=${ORGANIZATION}" || "")
```

1. Create a new sandbox on pantheon where the new site will reside

   - ```terminus site:create ${NEW_SITE_NAME} "${NEW_SITE_LABEL}" drupal9 ${ORG_COMMAND_SWITCH}```


2. Clone new site

   - ```terminus ```

3. terminus
4. Modules
5. Themes
6. Javascript libraries
7. Terminus plan:move
8. Terminus dns:move
