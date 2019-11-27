#!/usr/bin/php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// exercises
    // JJ = jumping jacks       | cardio
    // PS = push-ups            | bodyweight
    // FS = front squats        | strength
    // BS = back squats         | strength
    // PL = pull-ups            | bodyweight
    // RG = rings               | bodyweight
    // SS = short sprints       | cardio
    // HP = handstand practice  | bodyweight (calisthenic)
    // JR = jumping rope        | cardio
    //
    // BR = break

// timeframe
    // 30 exercises
    // 1min = exercises

// levels
    // beginner
    // advanced

// rules
    // advanced: 2xBR
    // beginner: 4xBR
    // program[0] != BR && program[30] != BR
    // beginner: count HP <= 1 // max 1
    // JJ, JR & SS - not adjacent | assert(program[i+0].type === cardio && program[i+0].type !== workout[i+1].type)
    // RG & PL // RGExercise::slots = 1 && PLExercise::slots = 1

// code
    // auto = non-interactive
    // php - this :)
    // no framework
    // explained, well...these comments :)
    // try some unit testing
    // html or stdout...I guess cli will do
    // predefined data (the members.txt file) // ./generator.php -f members.txt


// output
    // 00:00 - 01:00 Camille will do jumping jacks, Michael pushups, Tom will do front squat, Tim will do sprints Erik will do pull ups, Lars will do
    // 01:00 - 02:00 Camille will do front squat, Michael short sprints, Tom will do rings, Tim will do rings, Erik will do sprints
    // ...
    // 25:00 - 26:00 Camille will take a break, Michael short sprints, Tom will rings, Tim will take a break, Erik will do handstand practise, Lars pullups, Mathijs will take a break
    // ...
    // 29:00 - 30:00 Camille will do jumping jacks, Michael rings, Tom will do jumping jacks, Tim will do handstand practise, Erik will do front squats, Lars will do back squats, Mathijs will do short sprints

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

interface Stringable {
    public function __toString();
}

interface Jsonable {
    public function __toJSON();
}

// generator ///////////////////////////////////////////////////////////////////////////////////////////////////////////

class Member
{
    public const BEGINNER = 0;
    public const ADVANCED = 1;
    /** @var string */
    protected $name;
    /** @var int */
    protected $level = self::BEGINNER;
    /** @var Program */
    protected $program;
    /** @var Workout */
    protected $workout;
    /**
     * @param string $name
     * @param int    $level
     */
    public function __construct(string $name, int $level = self::BEGINNER)
    {
        Logger::dbg(__METHOD__);
        Logger::nfo('member:', $name, $level);
        $this->name  = $name;
        $this->level = $level;
    }
    /**
     * @param Program $p
     */
    public function setProgram(Program& $p) : void { $this->program = $p; }
    /**
     * @param Workout $w
     */
    public function setWorkout(Workout& $w) : void { $this->workout = $w; }
    /**
     * @return bool
     */
    public function isBeginner() : bool { return $this->level === self::BEGINNER; }
    /**
     * @return string
     */
    public function getName() : string { return $this->name; }
    
    public function getProgram() : Program { return $this->program; }
}

abstract class Exercise
{
    // slots
    public const UNLIMITED = -1;
    // types
    public const BREAK      = 0;
    public const CARDIO     = 1;
    public const BODYWEIGHT = 2;
    public const STRENGTH   = 3;
    /**
     * @var string $name
     */
    protected $name; // JJ
    /**
     * @var string $info
     */
    protected $info; // jumping-jacks
    /**
     * @var int $type;
     */
    protected $type = self::CARDIO; // cardio;
    /**
     * @var int $slots
     */
    protected $slots = self::UNLIMITED;
    /**
     * @var Member[] $members
     */
    protected $members = [];
    
    public function __construct(int $slots = self::UNLIMITED)
    {
        $this->slots = $slots;
    }
    
    abstract public function getName() : string;
    abstract public function getInfo() : string;
    abstract public function getType() : int;
    
    public function getSlots() : int { return $this->slots; }
}

class BRExercise extends Exercise
{
    public function getName() : string { return 'BR'; }
    public function getType() : int    { return Exercise::BREAK; }
    public function getInfo() : string { return 'break'; }
}

class JJExercise extends Exercise
{
    public function getName() : string { return 'JJ'; }
    public function getType() : int    { return Exercise::CARDIO; }
    public function getInfo() : string { return 'jumping jacks'; }
}

class RGExercise extends Exercise
{
    public function getName() : string { return 'RG'; }
    public function getType() : int    { return Exercise::BODYWEIGHT; }
    public function getInfo() : string { return 'rings'; }
}

class PSExercise extends Exercise
{
    public function getName() : string { return 'PS'; }
    public function getType() : int    { return Exercise::BODYWEIGHT; }
    public function getInfo() : string { return 'push-ups'; }
}

class FSExercise extends Exercise
{
    public function getName() : string { return 'FS'; }
    public function getType() : int    { return Exercise::STRENGTH; }
    public function getInfo() : string { return 'front squats'; }
}

class BSExercise extends Exercise
{
    public function getName() : string { return 'BS'; }
    public function getType() : int    { return Exercise::STRENGTH; }
    public function getInfo() : string { return 'back squats'; }
}

class PLExercise extends Exercise
{
    public function getName() : string { return 'PL'; }
    public function getType() : int    { return Exercise::BODYWEIGHT; }
    public function getInfo() : string { return 'pull-ups'; }
}

class SSExercise extends Exercise
{
    public function getName() : string { return 'SS'; }
    public function getType() : int    { return Exercise::CARDIO; }
    public function getInfo() : string { return 'short sprints'; }
}

class HSExercise extends Exercise
{
    public function getName() : string { return 'HS'; }
    public function getType() : int    { return Exercise::BODYWEIGHT; }
    public function getInfo() : string { return 'handstand practice'; }
}

class JRExercise extends Exercise
{
    public function getName() : string { return 'JR'; }
    public function getType() : int    { return Exercise::CARDIO; }
    public function getInfo() : string { return 'jumping rope'; }
}

abstract class Rule
{
    abstract public function __invoke(Workout& $w, Program& $p, Member& $m, int $t) : bool;
}

class NotAtEndsRule extends Rule
{
    public function __invoke(Workout& $w, Program& $p, Member& $m, int $t)  : bool
    {
        $f1st = $p->getExecise(0);
        if ($f1st && $f1st->getType() === Exercise::BREAK) {
            return false;
        }
        $last = $p->getExecise($p->getLength()-1);
        if ($last && $last->getType() === Exercise::BREAK) {
            return false;
        }
        return true;
    }
}

class NotAdjacentCardioRule extends Rule
{
    public function __invoke(Workout& $w, Program& $p, Member& $m, int $t)  : bool
    {
        for ($i = 1; $i < $p->getLength(); $i++) {
            $prev = $p->getExecise($i-1);
            $curr = $p->getExecise($i);
            if ($curr && $prev && $curr->getType() === Exercise::CARDIO && $prev->getType() === $curr->getType()) {
                return false;
            }
        }
        return true;
    }
}

class BeginnerMaxOneHandstandRule extends Rule
{
    public function __invoke(Workout& $w, Program& $p, Member& $m, int $t)  : bool
    {
        // if beginner
        if (!$m->isBeginner()) {
            $hcnt = 0;
            // for each exercise in program
            for ($i = 1; $i < $p->getLength(); $i++) {
                $curr = $p->getExecise($i);
                if ($curr && $curr->getName() === 'HS') {
                    $hcnt++;
                }
                // if count HS > 1
                if ($hcnt > 1) {
                    return false;
                }
            }
        }
        return true;
    }
}

class NoMoreThanSlotsAllowedRule extends Rule
{
    public function __invoke(Workout& $w, Program& $p, Member& $m, int $t)  : bool
    {
        $names = [];
        foreach($w->getMembers() as &$member) {
            /** @var Member $member */
            /** @var Program $program */
            $program = $member->getProgram();
            $exercise = $program->getExecise($t);
            // count exercises by name
            $names[$exercise->getName()] = isset($names[$exercise->getName()]) ? $names[$exercise->getName()] + 1 : 1;
        }
        // for each exercise type
        foreach ($names as $name => $count) {
            $slots = $w->getExercise($name)->getSlots();
            // if no. in use is greater than the no of allowed slots
            if ($slots !== Exercise::UNLIMITED && $count > $slots) {
                return false;
            }
        }
        // aok
        return true;
    }
}

class Program
{
    /**
     * @var Exercise[]
     */
    protected $exercises = [];
    /**
     * @var Workout $workout
     */
    protected $workout;
    /**
     * @var int $counter
     */
    protected $counter = 0;
    /**
     * @param int     $l
     * @param Workout $w
     */
    public function __construct(int $l, Workout& $w)
    {
        Logger::dbg(__METHOD__);
        $this->exercises = new SplFixedArray($l);
        $this->workout   = $w;
    }
    /**
     * @param int      $i
     * @param Exercise $e
     * @return bool
     */
    public function setExercise(int $i, Exercise& $e) : bool
    {
        Logger::dbg(__METHOD__);
        
        if (!empty($this->exercises[$i])) {
            return false;
        }
    
        $this->exercises[$i] = $e;
    
        $this->counter++;
        
        return true;
    }
    /**
     * @param int $i
     * @return Exercise
     */
    public function popExercise(int $i) : Exercise
    {
        Logger::dbg(__METHOD__);
        $e = $this->exercises[$i];
        $this->exercises[$i] = null;
        return $e;
    }
    /**
     * @return int
     */
    public function getLength() : int { return count($this->exercises); }
    
    public function getCount() : int { return $this->counter; }
    
    public function getExecise(int $i) : ?Exercise { return $this->exercises[$i]; }
    
    public function isComplete() : bool { return $this->counter === count($this->exercises); }
}

class Result implements Stringable, Jsonable
{
    protected $data = [];
    /**
     * @param int    $t
     * @param string $name
     * @param string $action
     */
    public function insert(int $t, string $name, string $action)
    {
        $this->data[$t] = $this->data[$t] ?? [];
        $this->data[$t][$name] = $action;
    }
    /**
     * @return array
     */
    public function __toJSON() : array
    {
        Logger::dbg(__METHOD__);
        $json = [];
        foreach ($this->data as $t => $actions) {
            $key = str_pad($t,2,'0', STR_PAD_LEFT) .' - '. str_pad($t+1,2,'0',STR_PAD_LEFT);
            foreach ($actions as $name => $action) {
                $json[$key][$name] = $action;
            }
        }
        
        return $json;
    }
    /**
     * @return string
     */
    public function __toString() : string
    {
        Logger::dbg(__METHOD__);
        $string = '';
        foreach ($this->data as $t => $actions) {
            $key = str_pad($t,2,'0', STR_PAD_LEFT) .' - '. str_pad($t+1,2,'0',STR_PAD_LEFT);
            $string .= $key .' ';
            foreach ($actions as $name => $action) {
                $string .= $name .' will do '. $action;
            }
        }
        return $string;
    }
}

class Pair
{
    /**
     * @var Member $member
     */
    public $member;
    /**
     * @var Exercise $exercise
     */
    public $exercise;
    /**
     * @param Member   $m
     * @param Exercise $e
     */
    public function __construct(Member &$m, Exercise &$e)
    {
        $this->member = $m;
        $this->exercise = $e;
    }
}

class Workout
{
    protected $length = 30;
    /**
     * @var Rule[] $rules
     */
    protected $rules = [];
    /**
     * @var Exercise[]
     */
    protected $exercises = [];
    /**
     * @var array $calendar
     */
    protected $calendar;
    /**
     * @var Member[]
     */
    protected $members = [];
    /**
     * @param int $length
     */
    public function __construct(int $length = 30)
    {
        Logger::dbg(__METHOD__);
        $this->length = $length;
        $this->calendar = new SplFixedArray($this->length);
        $this->init();
    }
    /**
     * override this to add other exercises
     */
    protected function init()
    {
        // make exercises
        $this->addExercise(new BRExercise());
        $this->addExercise(new JJExercise());
        $this->addExercise(new PSExercise());
        $this->addExercise(new FSExercise());
        $this->addExercise(new BSExercise());
        $this->addExercise(new SSExercise());
        $this->addExercise(new HSExercise());
        $this->addExercise(new JRExercise());
        $this->addExercise(new PLExercise(2));
        $this->addExercise(new RGExercise(2));
        // add rules
        $this->addRule(new NotAtEndsRule);
        $this->addRule(new NotAdjacentCardioRule);
        $this->addRule(new BeginnerMaxOneHandstandRule);
        $this->addRule(new NoMoreThanSlotsAllowedRule);
    }
    /**
     * @return int
     */
    public function getLength() : int { return $this->length; }
    /**
     * @param Member $m
     */
    public function addMember(Member& $m)
    {
        Logger::dbg(__METHOD__);
        $this->members[] = $m;
    }
    /**
     * @param Exercise $e
     */
    public function addExercise(Exercise $e) : void { $this->exercises[$e->getName()] = $e; }
    
    public function getExercise(string $name) : ?Exercise { return $this->exercises[$name]; }
    
    public function getMembers() : array { return $this->members; }
    
    public function addRule(Rule $r) { $this->rules[] = $r; }
    /**
     * @param Program  $p
     * @param Exercise $e
     * @param Member   $m
     * @return bool
     */
    protected function assign(Program& $p, Exercise& $e, Member& $m)
    {
        $t = rand(0,$this->length-1);
        // update exercises
        $k = $p->setExercise($t, $e);
        // for each rule
        foreach ($this->rules as &$rule) {
            /** @var Rule $rule */
            $k = $k && $rule($this,$p,$m,$t);
            // early stop
            if (!$k) {
                break;
            }
        }
        // remove if not ok
        if (!$k) {
            $p->popExercise($t);
        } else {
        // schedule
            $this->calendar[$t] = $this->calendar[$t] ?? [];
            $this->calendar[$t][] = new Pair($m, $e);
        }
        // passed all rules
        return $k;
    }
    
    /**
     * @return Result
     */
    public function start() : Result
    {
        Logger::dbg(__METHOD__);
        // rand members // so u don't get the same exercise pattern everytime
        shuffle($this->members);
        // for each member
        foreach ($this->members as &$member) {
            $program = new Program($this->length, $this);
            // set members program
            $member->setProgram($program);
            // add breaks
            $keys = array_fill(0,$member->isBeginner() ? 4 : 2, 'BR'); // BR
            do {
                $done     = false;
                $key      = current($keys);
                $exercise = &$this->exercises[$key];
                // try to assign a brake
                if ($this->assign($program, $exercise, $member)) {
                    $done = !next($keys);
                }
            } while (!$done);
            // done w/ breaks...this is where the actual exercise assignment starts
            // add exercises, filter-out break
            $keys = array_filter(array_keys($this->exercises), function ($key) { return $key !== Exercise::BREAK; });
            // shuffle the array - so that each member gets a different pattern
            shuffle($keys); // ['JJ','PS','FS','BS','PL','RG','SS','HP','JR'];
            // cache this, so we dont run the count function in 2billion times inside the do{}while loop
            $l = count($keys);
            do {
                $done = false;
                // current $key // 'FS'
                $key  = current($keys);
                // needed for swap-randomizer
                $i    = key($keys);
                // start over if at the end of the $keys array
                next($keys) || reset($keys);
                // who's next...
                $exercise = &$this->exercises[$key];
                // try to assign exercise to program for that member
                if ($this->assign($program, $exercise, $member)) {
                    // done, only if program has all time slots assigned
                    $done = $program->isComplete();
                } else {
                  // swap // make this even more complex...cause why not
                  $r   = $i === $l-1 ? rand(0, $i-1) : rand($i, $l-1);
                  $tmp = $keys[$i];
                  $keys[$i] = $keys[$r];
                  $keys[$r] = $tmp;
                }
            } while (!$done);
        }
        // for the output
        $result = new Result;
        // add calendar to result - did it this way to permite extension 
        foreach ($this->calendar as $t => $c) {
            // objects/classes more reliable than arrays with who-knows-what in them 
            foreach ($c as  $pair) {
                /** @var Pair $pair */
                $result->insert($t, $pair->member->getName(), $pair->exercise->getInfo());
            }
        }
        // return the result, duh...
        return $result;
    }
}

// log + cli ///////////////////////////////////////////////////////////////////////////////////////////////////////////

class Logger
{
    public const DEBUG = 0;
    public const INFO  = 1;
    public const WARN  = 2;
    public const QUIET = 9;
    
    protected static $level = self::QUIET;
    
    public static function level(int $level) : void { self::$level = $level; }
    
    public static function log(...$args) { array_map(function ($arg) { echo preg_replace('/\s+/',' ',print_r($arg,1)); echo ' '; }, $args); echo "\n"; }
    
    public static function dbg(...$args) { self::$level <= self::DEBUG ? self::log('[DBG]', ...$args) : null; }
    public static function nfo(...$args) { self::$level <= self::INFO  ? self::log('[NFO]', ...$args) : null; }
    public static function wrn(...$args) { self::$level <= self::WARN  ? self::log('[WRN]', ...$args) : null; }
}

abstract class Printer
{
    public const TEXT = 'text';
    public const JSON = 'json';
    
    abstract public function getType() : string;
}

class TextPrinter extends Printer
{
    public function getType() : string { return Printer::TEXT; }
    
    public function output(Stringable &$s)
    {
        echo $s->__toString(), "\n";
    }
}

class JsonPrinter extends Printer
{
    public function getType(): string { return Printer::JSON; }
    
    public function output(Jsonable &$j)
    {
        $json = $j->__toJSON();
        echo json_encode($json), "\n";
    }
}

class Command
{
    public $signature = '-f [file.ext] -o [txt|json] -v -vv -vvv';
    
    /**
     * @throws Exception
     */
    public function run() : void
    {
        global $argv, $argc;
    
        $file = 'members.txt';
        $type = 'text';
        
        for ($i = 1; $i < $argc && $arg = $argv[$i]; $i++) {
            switch ($arg) {
                case '-v'  : Logger::level(Logger::WARN);  break;
                case '-vv' : Logger::level(Logger::INFO);  break;
                case '-vvv': Logger::level(Logger::DEBUG); break;
                case '-f'  : $i++; $file = $argv[$i];             break;
                case '-o'  : $i++; $type = strtolower($argv[$i]); break;
            }
        }
    
        Logger::dbg(__METHOD__);
        
        if (!file_exists($file)) {
            throw new \Exception("File {$file} not found!");
        }
        
        if (!in_array($type, [Printer::TEXT,Printer::JSON])) {
            throw new \Exception("Unknown output {$type} format!");
        }
        
        $data = file_get_contents($file);
        
        $rows = explode("\n", $data);
        
        $workout = new Workout();
        
        foreach ($rows as $i => $row) {
            Logger::nfo($i, $row);
            $row = trim($row);
            if ($row[0] === '#') {
                continue;
            } else {
                list ($name,$beginner) = explode('|', $row);
                // @todo: if count($fields) === 0 warn and exclude
                $member = new Member($name, in_array($beginner,['yes','1','true']) ? Member::BEGINNER : Member::ADVANCED);
                $workout->addMember($member);
            }
        }
        
        $result = $workout->start();
        
        switch ($type) {
            default:
            case Printer::TEXT:
                $printer = new TextPrinter();
                $printer->output($result);
            break;
            case Printer::JSON:
                $printer = new JsonPrinter();
                $printer->output($result);
            break;
        }
    }
}

// run /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

try {
    (new Command())->run();
} catch (\Throwable $e) {
    echo $e->getMessage();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////