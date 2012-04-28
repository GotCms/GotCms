:
: ${PHPUNIT:="phpunit"}
: ${PHPUNIT_OPTS:="--verbose"}
: ${PHPUNIT_GROUPS:=}

while [ -n "$1" ] ; do
  case "$1" in
    ALL|all|MAX|max)
     PHPUNIT_GROUPS=""
     break ;;

    Es*|Datatypes*)
     PHPUNIT_GROUPS="${PHPUNIT_GROUPS:+"$PHPUNIT_GROUPS,"}$1"
     shift ;;
    Application*)
     PHPUNIT_GROUPS="${PHPUNIT_GROUPS:+"$PHPUNIT_GROUPS,"}$1"
     shift ;;

    *)
     PHPUNIT_GROUPS="${PHPUNIT_GROUPS:+"$PHPUNIT_GROUPS,"}Gc_$1"
     shift ;;
  esac
done

set -x
${PHPUNIT} ${PHPUNIT_OPTS} ${PHPUNIT_COVERAGE} ${PHPUNIT_DB} \
  ${PHPUNIT_GROUPS:+--group $PHPUNIT_GROUPS}

