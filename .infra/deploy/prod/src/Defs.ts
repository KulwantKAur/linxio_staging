export type DefEcsItem = {
  containers: {
    [name: string]: string
  },
  hooks?: {
    before: {
      [container: string]: string[]
    }[]
  }
}

export type DefEcs = {
  [service: string]: DefEcsItem
}

export type DefCronItem = {
  containers: {
    [name: string]: string
  },
}

export type DefCron = {
  [job: string]: DefCronItem
}

export type Def = {
  ecs: DefEcs,
  cron: DefCron,
}

export type Defs = {
  [mod: string]: Def
}
