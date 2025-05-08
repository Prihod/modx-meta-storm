<?xml version="1.0" encoding="UTF-8" ?>
<meta-storm xmlns="meta-storm">
    <definitions>

        <classMethod
                class="\\MODX\\Revolution\\modX"
                method="getObject"
                argument="0"
        >
            <collection name="{{SCHEMA_NAME}}" argument="0"  />
        </classMethod>

    </definitions>
    <collections>
        <xmlFile name="{{SCHEMA_NAME}}" xpath="$project/{{RELATIVE_PATH}}"/>
    </collections>
</meta-storm>