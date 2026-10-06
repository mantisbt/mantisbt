
```shell
tools/ecs/vendor/bin/ecs --config tools/ecs/config.php
```

```shell
tools/ecs/vendor/bin/ecs --config tools/ecs/config.php plugins/MantisCoreFormatting
```

```shell
tools/ecs/vendor/bin/ecs --config tools/ecs/config.php --fix
```


```shell
tools/ecs/vendor/bin/ecs --config tools/ecs/config.php plugins/MantisCoreFormatting --fix
```

### current changeset

```shell
tools/ecs/vendor/bin/ecs --config tools/ecs/config.php $(git diff --name-only)
```

#### --fix

```shell
tools/ecs/vendor/bin/ecs --config tools/ecs/config.php $(git diff --name-only) --fix
```
