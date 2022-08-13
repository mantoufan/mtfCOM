var AutoCompleteConfig = {
  placeholderHTML: mtfLang.get('标签'),
  initialList: 'tag',
  lists: {
    tag: [
      { value: '标签', children: 't' },
      { value: '技术', children: 'technology' },
      { value: '人', children: 'people' },
      { value: '制作团队', children: 'source' },
      { value: '画种', children: 'paint' },
      { value: '共享许可', children: 'share' },
      { value: '专题', children: 't' },
      { value: '来源', children: 't' }
    ],
    technology: [
      { value: '数据结构', children: 'datastructure' },
      { value: '算法', children: 'algorithm' },
      { value: '语言', children: 'language' },
      { value: '遍历', children: 'traversal' },
      { value: '查找', children: 'search' },
      { value: '排序', children: 'sort' },
      { value: '技巧', children: 'skill' },
      { value: '数学', children: 'maths' },
      { value: '操作系统', children: 'operatingsystem' },
      { value: '网络协议', children: 'networkprotocol' },
      { value: '框架', children: 'framework' },
      { value: '库', children: 'library' },
      { value: '工程化', children: 'engineering' },
      { value: '云原生', children: 'cloudnative' },
      { value: '数据库', children: 'database' },
      { value: '项目管理', children: 'projectmanagement' },
    ],
    datastructure: {
      allowFreeform: true,
      options: [
        '数组', 
        '字符串',
        '链表', 
        '栈',
        '队列', 
        '哈希表',
        '优先队列',
        '二叉树',
        '多叉树',
        '线段树',
        '字典树',
        '图',
        '并查集',
        '树状数组',
        '最小堆',
        '最大堆',
        '小根堆',
        '大根堆',
        '小顶堆',
        '大顶堆',
      ]
    },
    algorithm: {
      allowFreeform: true,
      options: [
        '递归', 
        '迭代', 
        '回溯', 
        '贪心算法',
        '动态规划',
        '哈希', 
        'KMP',
        '归并',
      ]
    },
    language: {
      allowFreeform: true,
      options: [
        'JavaScript',
        'Go',
        'PHP',
        'Python',
        'Rust',
        'Ruby',
        'C',
        'C++',
        'C#',
        'TypeScript',
        'Java',
        'HTML',
        'CSS'
      ]
    },
    traversal: {
      allowFreeform: true,
      options: [
        '顺序遍历',
        '倒序遍历',
        '前序遍历',
        '中序遍历',
        '后序遍历',
        '层序遍历',
        '螺旋遍历',
      ]
    },
    search: {
      allowFreeform: true,
      options: [
        '顺序查找',
        '二分查找',
        '三分查找',
        '深度优先搜索',
        '广度优先搜索',
      ]
    },
    sort: {
      allowFreeform: true,
      options: [
        '选择排序',
        '插入排序',
        '归并排序',
        '快速排序',
        '冒泡排序',
        '计数排序',
        '桶排序',
        '拓扑排序',
        '基数排序'
      ]
    },
    skill: {
      allowFreeform: true,
      options: [
        '双指针',
        '位运算',
        '前缀和',
        '滑动窗口',
        '状态压缩',
        '路径压缩',
        '哈希函数',
        '滚动哈希',
        '扫描线',
        '记忆化',
        '正则',
        '计数',
      ]
    },
    maths: {
      allowFreeform: true,
      options: [
        '求和',
        '快速幂',
        '质数',
        '曼哈顿距离',
        '中位数',
        '丑数',
        '杨辉三角',
        '排列',
        '组合',
        '水塘抽样',
        '拒绝抽样',
        '几何',
        '博弈',
        '数论',
        '概率与统计',
        '随机化'
      ]
    },
    operatingsystem: {
      allowFreeform: true,
      options: [
        '命令',
        '架构',
        '进程',
        '内存',
        '文件',
        '输入输出',
        '通信',
        '网络',
        '虚拟化',
        '容器化'
      ]
    },
    networkprotocol: {
      allowFreeform: true,
      options: [
        'IP',
        'TCP',
        'UDP',
        'HTTP',
        'DNS',
        'CDN',
        'WebSocket',
        'WebRTC',
        'Flannel',
        'Calico',
        'RPC',
        'SOAP',
        'RESTful',
      ]
    },
    framework: {
      allowFreeform: true,
      options: [
        'React',
        'Vue.js',
        'Svelte',
        'Angular',
        'Koa',
        'Express'
      ]
    },
    library: {
      allowFreeform: true,
      options: [
        'jQuery',
        'RxJS'
      ]
    },
    engineering: {
      allowFreeform: true,
      options: [
        'Node.js',
        'Webpack',
        'Babel',
        'Git'
      ]
    },
    cloudnative: {
      allowFreeform: true,
      options: [
        'Docker',
        'Kubernetes',
        'Nginx',
        'Apache',
        'Tomcat',
        'K8S',
        'K3S'
      ]
    },
    database: {
      allowFreeform: true,
      options: [
        '范式',
        '逆范式',
        '分区',
        '索引',
        '事务',
        '锁',
        '缓存',
        '分区',
        '分表',
        '读写分离',
        '主从复制'
      ]
    },
    projectmanagement: {
      allowFreeform: true,
      options: [
        '代码规范',
        '敏捷开发',
        '持续集成',
        '质量保证'
      ]
    },
    people: [
      { value: '萌点', children: 'moe' },
      { value: '服饰', children: 'cloth' },
      { value: '瞳色', children: 'eye' },
      { value: '发色', children: 'hair' }
    ],
    source: [
      { value: '作者', children: 't' },
      { value: '画师', children: 't' },
      { value: '模特', children: 't' },
      { value: '摄影师', children: 't' },
      { value: '化妆师', children: 't' },
      { value: '道具师', children: 't' }
    ],
    t: {
      allowFreeform: true,
      options: [mtfLang.get(['请', '输入'])]
    },
    moe: {
      allowFreeform: true,
      options: [
        '双马尾',
        '黑长直',
        '眨眼',
        '嘟嘴',
        '吐舌头',
        '兔耳朵',
        '猫耳朵',
        '猫爪爪',
        '天然呆',
        '爱哭鬼',
        '呆毛',
        '蝴蝶结',
        '戳戳脸',
        '平地摔',
        '嘘',
        '麻花辫',
        '齐刘海',
        'M字刘海',
        '剪刀手',
        '八字手势',
        '心形手势',
        'ILOVEYOU手势'
      ]
    },
    cloth: {
      allowFreeform: true,
      options: [
        '连衣裙',
        '蓬蓬裙',
        '礼服',
        '女仆装',
        '洋装',
        '校服',
        '水手服',
        '圣诞服',
        '汉服',
        '和服',
        '旗袍',
        '古装',
        '制服',
        '睡衣',
        '护士服',
        '洛丽塔'
      ]
    },
    eye: {
      allowFreeform: true,
      options: [
        '黑色眼睛',
        '金色眼睛',
        '棕色眼睛',
        '粉色眼睛',
        '蓝色眼睛',
        '绿色眼睛',
        '白色眼睛',
        '紫色眼睛',
        '灰色眼睛'
      ]
    },
    hair: {
      allowFreeform: true,
      options: [
        '黑色头发',
        '金色头发',
        '棕色头发',
        '粉色头发',
        '蓝色头发',
        '绿色头发',
        '白色头发',
        '紫色头发',
        '灰色头发'
      ]
    },
    paint: {
      allowFreeform: true,
      options: [
        '动漫',
        '儿童画',
        '彩笔画',
        '蜡笔画',
        '素描',
        '水彩画',
        '水粉画',
        '国画',
        '丙烯画',
        '工笔画',
        '写意画',
        '山水画',
        '花鸟画',
        '人物画',
        '静物画',
        '石膏几何体',
        '装饰画'
      ]
    },
    share: {
      allowFreeform: true,
      options: [
        '署名',
        '注明来源',
        '非商业使用',
        '禁止演绎',
        '相同方式共享',
        '禁止转载',
        '自由使用'
      ]
    },
    allowFreeform: true
  },
  onChange: function (n, o) {
    //new old 草稿
    var d = {}
    for (var _i in n) {
      var m = n[_i],
        k,
        v
      if (m.length > 2) {
        m.splice(0, 1)
      }
      for (var _j in m) {
        var a = m[_j]
        if (_j === '0') {
          k = a.value
        } else {
          v = a.value
          if (!BE(d, k)) {
            d[k] = []
          }
          d[k].push(v)
        }
      }
    }

    DRAFT[i]['key'] = d
    setTimeout(function () {
      BT.bt.tag_edi(A_TAG, $('#mtf-tag-key'))
    }, 1000)
  }
}